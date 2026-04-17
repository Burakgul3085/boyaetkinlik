<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', [
            'categories' => Category::query()
                ->with('parent')
                ->orderBy('parent_id')
                ->orderBy('nav_order')
                ->orderBy('name')
                ->get(),
            'parentCategories' => Category::query()
                ->whereNull('parent_id')
                ->orderBy('nav_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'nav_order' => ['nullable', 'integer', 'min:0'],
            'show_in_nav' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['show_in_nav'] = (bool) ($data['show_in_nav'] ?? false);
        $data['nav_order'] = (int) ($data['nav_order'] ?? 0);
        Category::query()->create($data);

        return back()->with('success', 'Kategori eklendi.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug,'.$category->id],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id', 'not_in:'.$category->id],
            'nav_order' => ['nullable', 'integer', 'min:0'],
            'show_in_nav' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['show_in_nav'] = (bool) ($data['show_in_nav'] ?? false);
        $data['nav_order'] = (int) ($data['nav_order'] ?? 0);
        $category->update($data);

        return back()->with('success', 'Kategori güncellendi.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Kategori silindi.');
    }
}
