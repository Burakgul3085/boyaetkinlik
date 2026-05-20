<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $allCategories = BlogCategory::query()->active()->ordered()->get();
        $subtreeCounts = BlogCategory::subtreeBlogCounts($allCategories, approvedOnly: true);

        $rootCategories = $allCategories->whereNull('parent_id')->values();

        $activeCategory = null;
        $categorySlug = $request->string('kategori')->toString();
        if ($categorySlug !== '') {
            $activeCategory = BlogCategory::query()->active()->where('slug', $categorySlug)->first();
        }

        $blogsQuery = Blog::query()->with('category')->approved()->latest();
        if ($activeCategory) {
            $categoryIds = BlogCategory::subtreeIdsIncludingSelf($activeCategory->id);
            $blogsQuery->whereIn('blog_category_id', $categoryIds);
        }

        $breadcrumbItems = [];
        $childCategories = collect();
        if ($activeCategory) {
            $breadcrumbItems = $activeCategory->breadcrumbItems();
            $activeCategory->load(['children' => fn ($q) => $q->active()->ordered()]);
            $childCategories = $activeCategory->children;
        }

        return view('frontend.blog.index', [
            'blogs' => $blogsQuery->paginate(9)->withQueryString(),
            'rootCategories' => $rootCategories,
            'subtreeCounts' => $subtreeCounts,
            'activeCategory' => $activeCategory,
            'breadcrumbItems' => $breadcrumbItems,
            'childCategories' => $childCategories,
        ]);
    }

    public function category(BlogCategory $blogCategory)
    {
        abort_unless($blogCategory->is_active, 404);

        $allCategories = BlogCategory::query()->active()->ordered()->get();
        $subtreeCounts = BlogCategory::subtreeBlogCounts($allCategories, approvedOnly: true);
        $rootCategories = $allCategories->whereNull('parent_id')->values();
        $categoryIds = BlogCategory::subtreeIdsIncludingSelf($blogCategory->id);

        $blogCategory->load(['children' => fn ($q) => $q->active()->ordered()]);

        return view('frontend.blog.index', [
            'blogs' => Blog::query()
                ->with('category')
                ->approved()
                ->whereIn('blog_category_id', $categoryIds)
                ->latest()
                ->paginate(9),
            'rootCategories' => $rootCategories,
            'subtreeCounts' => $subtreeCounts,
            'activeCategory' => $blogCategory,
            'breadcrumbItems' => $blogCategory->breadcrumbItems(),
            'childCategories' => $blogCategory->children,
        ]);
    }

    public function create()
    {
        $all = BlogCategory::query()->active()->ordered()->get();

        return view('frontend.blog.create', [
            'categoryOptions' => BlogCategory::orderedFlatWithDepth($all),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:400'],
            'content' => ['required', 'string', 'min:30'],
            'author_first_name' => ['required', 'string', 'max:100'],
            'author_last_name' => ['required', 'string', 'max:100'],
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'suggested_category_name' => ['nullable', 'string', 'max:120'],
            'image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:8192'],
        ]);

        $hasCategory = ! empty($data['blog_category_id']);
        $hasSuggestion = trim((string) ($data['suggested_category_name'] ?? '')) !== '';

        if (! $hasCategory && ! $hasSuggestion) {
            throw ValidationException::withMessages([
                'blog_category_id' => 'Lütfen bir kategori seçin veya yeni kategori önerin.',
            ]);
        }

        if ($hasCategory && $hasSuggestion) {
            $data['suggested_category_name'] = null;
        }

        if ($hasCategory) {
            $category = BlogCategory::query()->active()->find($data['blog_category_id']);
            if (! $category) {
                throw ValidationException::withMessages([
                    'blog_category_id' => 'Seçilen kategori geçersiz veya pasif.',
                ]);
            }
        }

        $payload = [
            'title' => $data['title'],
            'slug' => Blog::generateUniqueSlug($data['title']),
            'blog_category_id' => $hasCategory ? $data['blog_category_id'] : null,
            'suggested_category_name' => $hasCategory ? null : trim($data['suggested_category_name']),
            'excerpt' => $data['excerpt'],
            'content' => $data['content'],
            'author_first_name' => $data['author_first_name'],
            'author_last_name' => $data['author_last_name'],
            'status' => 'pending',
        ];

        if ($request->hasFile('image_file')) {
            $payload['image_path'] = $request->file('image_file')->store('blog-images', 'public');
        }

        Blog::query()->create($payload);

        return redirect()->route('blog.create')->with('success', 'Blog yazınız gönderildi. Admin onayından sonra yayına alınacaktır.');
    }

    public function show(Blog $blog)
    {
        abort_if($blog->status !== 'approved', 404);

        $blog->load('category');

        return view('frontend.blog.show', [
            'blog' => $blog,
            'recentBlogs' => Blog::query()
                ->with('category')
                ->approved()
                ->whereKeyNot($blog->id)
                ->when($blog->blog_category_id, fn ($q) => $q->where('blog_category_id', $blog->blog_category_id))
                ->latest()
                ->limit(4)
                ->get(),
        ]);
    }
}
