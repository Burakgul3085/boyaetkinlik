<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ColoringPage;

class CategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();
        $category->load('children');

        $categoryIds = $category->children->pluck('id')->push($category->id)->all();
        $coloringPages = ColoringPage::query()
            ->whereIn('category_id', $categoryIds)
            ->latest()
            ->get();

        return view('frontend.category', [
            'category' => $category,
            'coloringPages' => $coloringPages,
        ]);
    }
}
