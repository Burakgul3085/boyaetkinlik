<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ColoringPage;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();
        $category->load('children');

        $categoryIds = Category::subtreeIdsIncludingSelf($category->id);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'pricing' => ['nullable', 'in:all,free,paid'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'mode' => ['nullable', 'in:all,featured,latest'],
            'sort' => ['nullable', 'in:newest,oldest,title_asc,title_desc,price_asc,price_desc'],
        ]);

        $query = ColoringPage::query()
            ->whereIn('category_id', $categoryIds);

        $searchTerm = trim((string) ($filters['q'] ?? ''));
        if ($searchTerm !== '') {
            $query->where('title', 'like', '%'.$searchTerm.'%');
        }

        $pricing = $filters['pricing'] ?? 'all';
        if ($pricing === 'free') {
            $query->where('is_free', true);
        } elseif ($pricing === 'paid') {
            $query->where('is_free', false);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $mode = $filters['mode'] ?? 'all';
        if ($mode === 'featured') {
            $query->where('is_featured', true);
        } elseif ($mode === 'latest') {
            // "En Yeni" görünümünde son 24 saatte eklenen içerikleri listeler.
            $query->where('created_at', '>=', now()->subDay());
        }

        $sort = $filters['sort'] ?? 'newest';
        if ($sort === 'oldest') {
            $query->oldest();
        } elseif ($sort === 'title_asc') {
            $query->orderBy('title');
        } elseif ($sort === 'title_desc') {
            $query->orderByDesc('title');
        } elseif ($sort === 'price_asc') {
            $query->orderBy('is_free', 'desc')->orderBy('price');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc')->orderBy('is_free');
        } else {
            $query->latest();
        }

        $coloringPages = $query->paginate(18)->withQueryString();

        $categoryStatsBase = ColoringPage::query()->whereIn('category_id', $categoryIds);
        $categoryTotalCount = (clone $categoryStatsBase)->count();
        $categoryFreeCount = (clone $categoryStatsBase)->where('is_free', true)->count();
        $categoryPaidCount = (clone $categoryStatsBase)->where('is_free', false)->count();
        $categoryFeaturedCount = (clone $categoryStatsBase)->where('is_featured', true)->count();

        $breadcrumbItems = $category->ancestorChain()->map(function ($ancestor) {
            return [
                'label' => $ancestor->name,
                'url' => route('categories.show', ['slug' => $ancestor->slug]),
            ];
        })->push([
            'label' => $category->name,
            'url' => null,
        ])->all();

        return view('frontend.category', [
            'category' => $category,
            'breadcrumbItems' => $breadcrumbItems,
            'coloringPages' => $coloringPages,
            'categoryTotalCount' => $categoryTotalCount,
            'categoryFreeCount' => $categoryFreeCount,
            'categoryPaidCount' => $categoryPaidCount,
            'categoryFeaturedCount' => $categoryFeaturedCount,
            'activeFilters' => [
                'q' => $searchTerm,
                'pricing' => $pricing,
                'date_from' => $filters['date_from'] ?? null,
                'date_to' => $filters['date_to'] ?? null,
                'mode' => $mode,
                'sort' => $sort,
            ],
        ]);
    }
}
