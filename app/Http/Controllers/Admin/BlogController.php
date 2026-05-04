<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function index()
    {
        return view('admin.blogs.index', [
            'pendingBlogs' => Blog::query()->where('status', 'pending')->latest()->get(),
            'approvedBlogs' => Blog::query()->where('status', 'approved')->latest()->get(),
            'rejectedBlogs' => Blog::query()->where('status', 'rejected')->latest()->get(),
        ]);
    }

    public function update(Request $request, Blog $blog): RedirectResponse
    {
        $data = $request->validate([
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

        // Başlık değiştiyse slug çakışmadan tazelensin.
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

    public function approve(Blog $blog): RedirectResponse
    {
        $blog->update([
            'status' => 'approved',
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
