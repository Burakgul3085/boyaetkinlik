<?php

namespace App\Http\Controllers;

use App\Models\Experiment;
use App\Models\ExperimentCategory;
use App\Support\OnlineExperimentLab;
use Illuminate\Http\Request;

class ExperimentController extends Controller
{
    public function index(Request $request)
    {
        $allCategories = ExperimentCategory::query()->active()->ordered()->get();
        $subtreeCounts = ExperimentCategory::subtreeExperimentCounts($allCategories, publishedOnly: true);

        $activeCategory = null;
        $categorySlug = $request->string('kategori')->toString();
        if ($categorySlug !== '') {
            $activeCategory = ExperimentCategory::query()->active()->where('slug', $categorySlug)->first();
        }

        $experimentsQuery = Experiment::query()->with('category')->published()->latest();
        if ($activeCategory) {
            $categoryIds = ExperimentCategory::subtreeIdsIncludingSelf($activeCategory->id);
            $experimentsQuery->whereIn('experiment_category_id', $categoryIds);
        }

        $breadcrumbItems = [];
        if ($activeCategory) {
            $breadcrumbItems = $activeCategory->breadcrumbItems();
        }

        return view('frontend.experiments.index', [
            'experiments' => $experimentsQuery->paginate(9)->withQueryString(),
            'subtreeCounts' => $subtreeCounts,
            'activeCategory' => $activeCategory,
            'breadcrumbItems' => $breadcrumbItems,
            'categoryFilterTree' => ExperimentCategory::buildActiveFilterTree($allCategories),
            'activePathIds' => ExperimentCategory::activePathIds($activeCategory),
            'totalExperimentCount' => Experiment::query()->published()->count(),
            'onlineLabCount' => OnlineExperimentLab::playableCount(),
        ]);
    }

    public function category(ExperimentCategory $experimentCategory)
    {
        abort_unless($experimentCategory->is_active, 404);

        $allCategories = ExperimentCategory::query()->active()->ordered()->get();
        $subtreeCounts = ExperimentCategory::subtreeExperimentCounts($allCategories, publishedOnly: true);
        $categoryIds = ExperimentCategory::subtreeIdsIncludingSelf($experimentCategory->id);

        return view('frontend.experiments.index', [
            'experiments' => Experiment::query()
                ->with('category')
                ->published()
                ->whereIn('experiment_category_id', $categoryIds)
                ->latest()
                ->paginate(9),
            'subtreeCounts' => $subtreeCounts,
            'activeCategory' => $experimentCategory,
            'breadcrumbItems' => $experimentCategory->breadcrumbItems(),
            'categoryFilterTree' => ExperimentCategory::buildActiveFilterTree($allCategories),
            'activePathIds' => ExperimentCategory::activePathIds($experimentCategory),
            'totalExperimentCount' => Experiment::query()->published()->count(),
            'onlineLabCount' => OnlineExperimentLab::playableCount(),
        ]);
    }

    public function show(Experiment $experiment)
    {
        abort_unless($experiment->isPublished(), 404);

        $experiment->load('category');

        return view('frontend.experiments.show', [
            'experiment' => $experiment,
            'recentExperiments' => Experiment::query()
                ->with('category')
                ->published()
                ->whereKeyNot($experiment->id)
                ->when($experiment->experiment_category_id, fn ($q) => $q->where('experiment_category_id', $experiment->experiment_category_id))
                ->latest()
                ->limit(4)
                ->get(),
        ]);
    }
}
