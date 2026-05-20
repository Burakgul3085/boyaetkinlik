<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        BlogCategory::query()->create([
            'name' => $data['name'],
            'slug' => BlogCategory::generateUniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => true,
            'source' => 'admin',
        ]);

        return back()->with('success', 'Blog kategorisi eklendi.');
    }

    public function update(Request $request, BlogCategory $blogCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'name' => $data['name'],
            'sort_order' => (int) ($data['sort_order'] ?? $blogCategory->sort_order),
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? $blogCategory->is_active),
        ];

        if ($blogCategory->name !== $data['name']) {
            $payload['slug'] = BlogCategory::generateUniqueSlug($data['name']);
        }

        $blogCategory->update($payload);

        return back()->with('success', 'Blog kategorisi güncellendi.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        if ($blogCategory->blogs()->exists()) {
            return back()->withErrors([
                'blog_category' => 'Bu kategoride blog yazısı var; silinemez. Pasif yapabilirsiniz — yazılar silinmez.',
            ]);
        }

        $blogCategory->delete();

        return back()->with('success', 'Blog kategorisi silindi.');
    }
}
