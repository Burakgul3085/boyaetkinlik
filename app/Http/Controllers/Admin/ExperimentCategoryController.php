<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExperimentCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExperimentCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'parent_id' => ['nullable', 'integer', 'exists:experiment_categories,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        ExperimentCategory::query()->create([
            'name' => $data['name'],
            'slug' => ExperimentCategory::generateUniqueSlug($data['name']),
            'parent_id' => ! empty($data['parent_id']) ? (int) $data['parent_id'] : null,
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => true,
            'source' => 'admin',
        ]);

        return back()->with('success', 'Deney kategorisi eklendi.');
    }

    public function update(Request $request, ExperimentCategory $experimentCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'parent_id' => ['nullable', 'integer', 'exists:experiment_categories,id', 'not_in:'.$experimentCategory->id],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'name' => $data['name'],
            'sort_order' => (int) ($data['sort_order'] ?? $experimentCategory->sort_order),
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? $experimentCategory->is_active),
        ];

        if (! empty($data['parent_id'])) {
            $parentId = (int) $data['parent_id'];
            if (in_array($parentId, ExperimentCategory::forbiddenParentIdsFor($experimentCategory), true)) {
                return back()->withErrors([
                    'parent_id' => 'Üst kategori geçersiz: kendi alt kategorinizi seçemezsiniz.',
                ])->withInput();
            }
            $payload['parent_id'] = $parentId;
        } else {
            $payload['parent_id'] = null;
        }

        if ($experimentCategory->name !== $data['name']) {
            $payload['slug'] = ExperimentCategory::generateUniqueSlug($data['name']);
        }

        $experimentCategory->update($payload);

        return back()->with('success', 'Deney kategorisi güncellendi.');
    }

    public function destroy(ExperimentCategory $experimentCategory): RedirectResponse
    {
        if ($experimentCategory->children()->exists()) {
            return back()->withErrors([
                'experiment_category' => 'Bu kategorinin alt kategorileri var; önce onları silin veya başka üste taşıyın.',
            ]);
        }

        if ($experimentCategory->experiments()->exists()) {
            return back()->withErrors([
                'experiment_category' => 'Bu kategoride deney var; silinemez. Pasif yapabilirsiniz.',
            ]);
        }

        $experimentCategory->delete();

        return back()->with('success', 'Deney kategorisi silindi.');
    }
}
