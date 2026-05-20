<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'parent_id' => ['nullable', 'integer', 'exists:blog_categories,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        BlogCategory::query()->create([
            'name' => $data['name'],
            'slug' => BlogCategory::generateUniqueSlug($data['name']),
            'parent_id' => ! empty($data['parent_id']) ? (int) $data['parent_id'] : null,
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
            'parent_id' => ['nullable', 'integer', 'exists:blog_categories,id', 'not_in:'.$blogCategory->id],
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

        if (! empty($data['parent_id'])) {
            $parentId = (int) $data['parent_id'];
            if (in_array($parentId, BlogCategory::forbiddenParentIdsFor($blogCategory), true)) {
                return back()->withErrors([
                    'parent_id' => 'Üst kategori geçersiz: kendi alt kategorinizi seçemezsiniz.',
                ])->withInput();
            }
            $payload['parent_id'] = $parentId;
        } else {
            $payload['parent_id'] = null;
        }

        if ($blogCategory->name !== $data['name']) {
            $payload['slug'] = BlogCategory::generateUniqueSlug($data['name']);
        }

        $blogCategory->update($payload);

        return back()->with('success', 'Blog kategorisi güncellendi.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        if ($blogCategory->children()->exists()) {
            return back()->withErrors([
                'blog_category' => 'Bu kategorinin alt kategorileri var; önce onları silin veya başka üste taşıyın.',
            ]);
        }

        if ($blogCategory->blogs()->exists()) {
            return back()->withErrors([
                'blog_category' => 'Bu kategoride blog yazısı var; silinemez. Pasif yapabilirsiniz — yazılar silinmez.',
            ]);
        }

        $blogCategory->delete();

        return back()->with('success', 'Blog kategorisi silindi.');
    }
}
