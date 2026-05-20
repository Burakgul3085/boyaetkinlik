<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BlogController extends Controller
{
    public function index()
    {
        $blogCategories = BlogCategory::query()
            ->withCount('blogs')
            ->ordered()
            ->get();

        return view('admin.blogs.index', [
            'blogCategories' => $blogCategories,
            'activeCategories' => BlogCategory::query()->active()->ordered()->get(),
            'pendingBlogs' => Blog::query()->with('category')->where('status', 'pending')->latest()->get(),
            'approvedBlogs' => Blog::query()->with('category')->where('status', 'approved')->latest()->get(),
            'rejectedBlogs' => Blog::query()->with('category')->where('status', 'rejected')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'blog_category_id' => ['required', 'exists:blog_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:400'],
            'content' => ['required', 'string', 'min:10'],
            'author_first_name' => ['required', 'string', 'max:100'],
            'author_last_name' => ['required', 'string', 'max:100'],
            'image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:8192'],
        ]);

        $payload = [
            'blog_category_id' => $data['blog_category_id'],
            'title' => $data['title'],
            'slug' => Blog::generateUniqueSlug($data['title']),
            'excerpt' => $data['excerpt'],
            'content' => $data['content'],
            'author_first_name' => $data['author_first_name'],
            'author_last_name' => $data['author_last_name'],
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'suggested_category_name' => null,
        ];

        if ($request->hasFile('image_file')) {
            $payload['image_path'] = $request->file('image_file')->store('blog-images', 'public');
        }

        Blog::query()->create($payload);

        return back()->with('success', 'Blog yazısı yayınlandı.');
    }

    public function update(Request $request, Blog $blog): RedirectResponse
    {
        $data = $request->validate([
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:400'],
            'content' => ['required', 'string', 'min:10'],
            'author_first_name' => ['required', 'string', 'max:100'],
            'author_last_name' => ['required', 'string', 'max:100'],
            'image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:8192'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'title' => $data['title'],
            'excerpt' => $data['excerpt'],
            'content' => $data['content'],
            'author_first_name' => $data['author_first_name'],
            'author_last_name' => $data['author_last_name'],
        ];

        if ($blog->status === 'approved' && ! empty($data['blog_category_id'])) {
            $payload['blog_category_id'] = $data['blog_category_id'];
        }

        if ($blog->title !== $data['title']) {
            $payload['slug'] = Blog::generateUniqueSlug($data['title']);
        }

        if (! empty($data['remove_image']) && $blog->image_path) {
            Storage::disk('public')->delete($blog->image_path);
            $payload['image_path'] = null;
        }

        if ($request->hasFile('image_file')) {
            if ($blog->image_path) {
                Storage::disk('public')->delete($blog->image_path);
            }
            $payload['image_path'] = $request->file('image_file')->store('blog-images', 'public');
        }

        $blog->update($payload);

        return back()->with('success', 'Blog yazısı güncellendi.');
    }

    public function approve(Request $request, Blog $blog): RedirectResponse
    {
        $data = $request->validate([
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'category_name' => ['nullable', 'string', 'max:120'],
        ]);

        $selectedCategoryId = $request->filled('blog_category_id') ? (int) $data['blog_category_id'] : null;

        if ($selectedCategoryId === null && trim((string) ($data['category_name'] ?? '')) === '' && ! $blog->blog_category_id && trim((string) $blog->suggested_category_name) === '') {
            throw ValidationException::withMessages([
                'category_name' => 'Onay için listeden kategori seçin veya kategori adını düzenleyin.',
            ]);
        }

        $categoryId = Blog::resolveCategoryIdForApproval(
            $blog,
            $selectedCategoryId,
            $data['category_name'] ?? null
        );

        $blog->update([
            'status' => 'approved',
            'blog_category_id' => $categoryId,
            'suggested_category_name' => null,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Blog yazısı onaylandı ve yayına alındı.');
    }

    public function reject(Blog $blog): RedirectResponse
    {
        $blog->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return back()->with('warning', 'Blog yazısı reddedildi.');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        if ($blog->image_path) {
            Storage::disk('public')->delete($blog->image_path);
        }

        $blog->delete();

        return back()->with('success', 'Blog yazısı silindi.');
    }
}
