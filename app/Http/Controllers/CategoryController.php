<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        $category->load(['coloringPages' => fn ($query) => $query->latest()]);

        return view('frontend.category', compact('category'));
    }
}
