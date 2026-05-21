<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Experiment;
use App\Models\ExperimentCategory;
use App\Support\OnlineExperimentLab;
use App\Support\YoutubeEmbed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ExperimentController extends Controller
{
    public function index(Request $request)
    {
        $allExperimentCategories = ExperimentCategory::allForAdminTree()->loadCount('experiments');

        $statusFilter = $request->string('durum')->toString();
        $categoryFilter = $request->integer('kategori') ?: null;
        $search = trim($request->string('ara')->toString());

        $baseQuery = Experiment::query()->with('category')->latest();

        if (in_array($statusFilter, ['published', 'draft'], true)) {
            $baseQuery->where('status', $statusFilter);
        }

        if ($categoryFilter) {
            $category = ExperimentCategory::query()->find($categoryFilter);
            if ($category) {
                $ids = ExperimentCategory::subtreeIdsIncludingSelf($category->id);
                $baseQuery->whereIn('experiment_category_id', $ids);
            }
        }

        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search): void {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('excerpt', 'like', '%'.$search.'%');
            });
        }

        $filtered = (clone $baseQuery)->get();

        return view('admin.experiments.index', [
            'onlineLabTypes' => OnlineExperimentLab::types(),
            'experimentCategories' => $allExperimentCategories,
            'parentBreadcrumbLabels' => ExperimentCategory::parentBreadcrumbLabelsFor($allExperimentCategories),
            'parentSelectOptionsCreate' => ExperimentCategory::orderedFlatForParentSelect(null, $allExperimentCategories),
            'parentSelectOptionsForEdit' => ExperimentCategory::parentSelectOptionsForAllEdits($allExperimentCategories),
            'subtreeExperimentCounts' => ExperimentCategory::subtreeExperimentCounts($allExperimentCategories),
            'categoryAssignmentOptions' => ExperimentCategory::orderedFlatWithDepth($allExperimentCategories),
            'publishedExperiments' => $filtered->where('status', 'published')->values(),
            'draftExperiments' => $filtered->where('status', 'draft')->values(),
            'statusFilter' => $statusFilter,
            'categoryFilter' => $categoryFilter,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedExperimentPayload($request, requireCategory: true);

        $payload = [
            'experiment_category_id' => $data['experiment_category_id'],
            'title' => $data['title'],
            'slug' => Experiment::generateUniqueSlug($data['title']),
            'excerpt' => $data['excerpt'],
            'content' => $data['content'],
            'youtube_url' => $data['youtube_url'],
            'author_first_name' => $data['author_first_name'],
            'author_last_name' => $data['author_last_name'],
            'status' => $data['publish_now'] ? 'published' : 'draft',
            'published_at' => $data['publish_now'] ? now() : null,
            'published_by' => $data['publish_now'] ? auth()->id() : null,
        ];

        if ($request->hasFile('image_file')) {
            $payload['image_path'] = $request->file('image_file')->store('experiment-images', 'public');
        }

        $payload = array_merge($payload, $this->onlineLabFieldsFromRequest($request));

        Experiment::query()->create($payload);

        return back()->with('success', $data['publish_now'] ? 'Deney yayınlandı.' : 'Deney taslak olarak kaydedildi.');
    }

    public function update(Request $request, Experiment $experiment): RedirectResponse
    {
        $data = $this->validatedExperimentPayload($request, requireCategory: $experiment->status === 'published');

        $payload = [
            'title' => $data['title'],
            'excerpt' => $data['excerpt'],
            'content' => $data['content'],
            'youtube_url' => $data['youtube_url'],
            'author_first_name' => $data['author_first_name'],
            'author_last_name' => $data['author_last_name'],
        ];

        if ($experiment->status === 'published') {
            $payload['experiment_category_id'] = $data['experiment_category_id'];
        }

        if ($experiment->title !== $data['title']) {
            $payload['slug'] = Experiment::generateUniqueSlug($data['title']);
        }

        if (! empty($data['remove_image']) && $experiment->image_path) {
            Storage::disk('public')->delete($experiment->image_path);
            $payload['image_path'] = null;
        }

        if ($request->hasFile('image_file')) {
            if ($experiment->image_path) {
                Storage::disk('public')->delete($experiment->image_path);
            }
            $payload['image_path'] = $request->file('image_file')->store('experiment-images', 'public');
        }

        $experiment->update(array_merge($payload, $this->onlineLabFieldsFromRequest($request)));

        return back()
            ->withInput(['_edit_experiment_id' => $experiment->id])
            ->with('success', 'Deney güncellendi.');
    }

    public function publish(Experiment $experiment): RedirectResponse
    {
        if (! $experiment->experiment_category_id) {
            return back()->withErrors([
                'experiment' => 'Yayınlamak için önce bir kategori atayın.',
            ]);
        }

        $experiment->update([
            'status' => 'published',
            'published_at' => now(),
            'published_by' => auth()->id(),
        ]);

        return back()->with('success', 'Deney yayına alındı.');
    }

    public function unpublish(Experiment $experiment): RedirectResponse
    {
        $experiment->update([
            'status' => 'draft',
            'published_at' => null,
            'published_by' => null,
        ]);

        return back()->with('warning', 'Deney yayından kaldırıldı (taslak).');
    }

    public function destroy(Experiment $experiment): RedirectResponse
    {
        if ($experiment->image_path) {
            Storage::disk('public')->delete($experiment->image_path);
        }

        $experiment->delete();

        return back()->with('success', 'Deney silindi.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedExperimentPayload(Request $request, bool $requireCategory): array
    {
        $rules = [
            'experiment_category_id' => [$requireCategory ? 'required' : 'nullable', 'integer', 'exists:experiment_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:400'],
            'content' => ['required', 'string', 'min:10'],
            'author_first_name' => ['required', 'string', 'max:100'],
            'author_last_name' => ['required', 'string', 'max:100'],
            'youtube_url' => ['nullable', 'string', 'max:500'],
            'image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:8192'],
            'remove_image' => ['nullable', 'boolean'],
            'publish_now' => ['nullable', 'boolean'],
            'online_lab_enabled' => ['nullable', 'boolean'],
            'online_lab_type' => ['nullable', 'string', 'max:40'],
            'online_lab_age_label' => ['nullable', 'string', 'max:40'],
            'online_lab_duration_label' => ['nullable', 'string', 'max:40'],
            'online_lab_sort_order' => ['nullable', 'integer', 'min:0'],
        ];

        $data = $request->validate($rules);

        $youtube = trim((string) ($data['youtube_url'] ?? ''));
        if ($youtube !== '' && YoutubeEmbed::extractId($youtube) === null) {
            throw ValidationException::withMessages([
                'youtube_url' => 'Geçerli bir YouTube bağlantısı girin (watch, youtu.be veya embed).',
            ]);
        }

        $data['youtube_url'] = $youtube !== '' ? $youtube : null;
        $data['publish_now'] = (bool) ($data['publish_now'] ?? false);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function onlineLabFieldsFromRequest(Request $request): array
    {
        $enabled = $request->boolean('online_lab_enabled');
        $type = $request->string('online_lab_type')->toString() ?: null;

        if ($enabled) {
            if (! OnlineExperimentLab::isValidType($type)) {
                throw ValidationException::withMessages([
                    'online_lab_type' => 'Online laboratuvar için geçerli bir deney tipi seçin.',
                ]);
            }
            if (! OnlineExperimentLab::isPlayable($type)) {
                throw ValidationException::withMessages([
                    'online_lab_type' => 'Seçilen deney tipi henüz yayında değil.',
                ]);
            }
        } else {
            $type = null;
        }

        return [
            'online_lab_enabled' => $enabled,
            'online_lab_type' => $type,
            'online_lab_age_label' => trim((string) $request->input('online_lab_age_label', '')) ?: null,
            'online_lab_duration_label' => trim((string) $request->input('online_lab_duration_label', '')) ?: null,
            'online_lab_sort_order' => (int) $request->input('online_lab_sort_order', 0),
        ];
    }
}
