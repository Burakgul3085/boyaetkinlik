<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'icon_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'cover_image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['show_in_nav'] = (bool) ($data['show_in_nav'] ?? false);
        $data['nav_order'] = (int) ($data['nav_order'] ?? 0);
        if ($request->hasFile('icon_file')) {
            $data['icon_path'] = $request->file('icon_file')->store('category-icons', 'public');
        }
        if ($request->hasFile('cover_image_file')) {
            $data['cover_image_path'] = $request->file('cover_image_file')->store('category-covers', 'public');
        }
        unset($data['icon_file']);
        unset($data['cover_image_file']);
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
            'icon_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'cover_image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['show_in_nav'] = (bool) ($data['show_in_nav'] ?? false);
        $data['nav_order'] = (int) ($data['nav_order'] ?? 0);
        if ($request->hasFile('icon_file')) {
            if ($category->icon_path) {
                Storage::disk('public')->delete($category->icon_path);
            }
            $data['icon_path'] = $request->file('icon_file')->store('category-icons', 'public');
        }
        if ($request->hasFile('cover_image_file')) {
            if ($category->cover_image_path) {
                Storage::disk('public')->delete($category->cover_image_path);
            }
            $data['cover_image_path'] = $request->file('cover_image_file')->store('category-covers', 'public');
        }
        unset($data['icon_file']);
        unset($data['cover_image_file']);
        $category->update($data);

        return back()->with('success', 'Kategori güncellendi.');
    }

    public function destroy(Category $category)
    {
        if ($category->icon_path) {
            Storage::disk('public')->delete($category->icon_path);
        }
        if ($category->cover_image_path) {
            Storage::disk('public')->delete($category->cover_image_path);
        }
        $category->delete();
        return back()->with('success', 'Kategori silindi.');
    }
}
