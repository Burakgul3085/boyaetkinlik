<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ColoringPage;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'pricing' => ['nullable', 'in:all,free,paid'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'mode' => ['nullable', 'in:all,featured,latest'],
            'sort' => ['nullable', 'in:newest,oldest,title_asc,title_desc,price_asc,price_desc'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $allCategories = Category::query()
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('nav_order')
            ->orderBy('name')
            ->get();

        $selectedCategory = null;
        $categoryIds = [];
        if (! empty($filters['category_id'])) {
            $selectedCategory = Category::query()->with('children')->find($filters['category_id']);
            if ($selectedCategory) {
                $categoryIds = $selectedCategory->children->pluck('id')->push($selectedCategory->id)->all();
            }
        }

        $query = ColoringPage::query()->with('category');

        if (! empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        $searchTerm = trim((string) ($filters['q'] ?? ''));
        if ($searchTerm !== '') {
            $variants = $this->searchLikeVariants($searchTerm);
            $query->where(function ($outer) use ($variants) {
                foreach ($variants as $term) {
                    $pattern = '%'.$this->escapeLike($term).'%';
                    $outer->orWhere(function ($q) use ($pattern) {
                        $q->where('coloring_pages.title', 'like', $pattern)
                            ->orWhere('coloring_pages.description', 'like', $pattern)
                            ->orWhereHas('category', function ($cq) use ($pattern) {
                                $cq->where('name', 'like', $pattern)
                                    ->orWhere('description', 'like', $pattern)
                                    ->orWhere('slug', 'like', $pattern);
                            })
                            ->orWhereHas('category.parent', function ($pq) use ($pattern) {
                                $pq->where('name', 'like', $pattern)
                                    ->orWhere('description', 'like', $pattern);
                            });
                    });
                }
            });
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

        $filteredPages = $query->paginate(12)->withQueryString();

        $searchCategoryMatches = collect();
        $searchPageMatches = collect();
        if ($searchTerm !== '') {
            $variants = $this->searchLikeVariants($searchTerm);

            $searchCategoryMatches = Category::query()
                ->with('parent')
                ->where(function ($q) use ($variants) {
                    foreach ($variants as $term) {
                        $pattern = '%'.$this->escapeLike($term).'%';
                        $q->orWhere('name', 'like', $pattern)
                            ->orWhere('description', 'like', $pattern)
                            ->orWhere('slug', 'like', $pattern)
                            ->orWhereHas('parent', function ($parentQuery) use ($pattern) {
                                $parentQuery->where('name', 'like', $pattern);
                            })
                            ->orWhereHas('coloringPages', function ($pageQuery) use ($pattern) {
                                $pageQuery->where('title', 'like', $pattern)
                                    ->orWhere('description', 'like', $pattern);
                            });
                    }
                })
                ->orderBy('name')
                ->limit(8)
                ->get();

            $searchPageMatches = ColoringPage::query()
                ->with('category')
                ->where(function ($q) use ($variants) {
                    foreach ($variants as $term) {
                        $pattern = '%'.$this->escapeLike($term).'%';
                        $q->orWhere('title', 'like', $pattern)
                            ->orWhere('description', 'like', $pattern)
                            ->orWhereHas('category', function ($categoryQuery) use ($pattern) {
                                $categoryQuery->where('name', 'like', $pattern)
                                    ->orWhere('description', 'like', $pattern)
                                    ->orWhereHas('parent', function ($parentQuery) use ($pattern) {
                                        $parentQuery->where('name', 'like', $pattern);
                                    });
                            });
                    }
                })
                ->latest()
                ->limit(8)
                ->get();
        }

        $totalPagesCount = ColoringPage::query()->count();
        $totalFreePagesCount = ColoringPage::query()->where('is_free', true)->count();
        $totalPaidPagesCount = ColoringPage::query()->where('is_free', false)->count();
        $paidMarqueePages = ColoringPage::query()
            ->with('category')
            ->where('is_free', false)
            ->latest()
            ->get();

        return view('frontend.home', [
            'categories' => Category::query()->latest()->get(),
            'featuredPages' => ColoringPage::query()->latest()->take(8)->get(),
            'featuredCount' => ColoringPage::query()->where('is_featured', true)->count(),
            'totalPagesCount' => $totalPagesCount,
            'totalFreePagesCount' => $totalFreePagesCount,
            'totalPaidPagesCount' => $totalPaidPagesCount,
            'paidMarqueePages' => $paidMarqueePages,
            'allCategories' => $allCategories,
            'filteredPages' => $filteredPages,
            'searchCategoryMatches' => $searchCategoryMatches,
            'searchPageMatches' => $searchPageMatches,
            'activeFilters' => [
                'q' => $searchTerm,
                'pricing' => $pricing,
                'date_from' => $filters['date_from'] ?? null,
                'date_to' => $filters['date_to'] ?? null,
                'mode' => $mode,
                'sort' => $sort,
                'category_id' => $filters['category_id'] ?? null,
            ],
            'adsHeader' => Setting::getValue('ads_header'),
            'adsLeft' => Setting::getValue('ads_left'),
            'adsRight' => Setting::getValue('ads_right'),
        ]);
    }

    /**
     * @return list<string>
     */
    private function searchLikeVariants(string $raw): array
    {
        $t = trim($raw);
        if ($t === '') {
            return [];
        }

        $lower = mb_strtolower($t, 'UTF-8');
        $ascii = strtr($lower, [
            'ş' => 's', 'ğ' => 'g', 'ü' => 'u', 'ö' => 'o', 'ç' => 'c', 'ı' => 'i',
            'Ş' => 's', 'Ğ' => 'g', 'Ü' => 'u', 'Ö' => 'o', 'Ç' => 'c', 'İ' => 'i',
        ]);

        return array_values(array_unique(array_filter([$t, $lower, $ascii])));
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
