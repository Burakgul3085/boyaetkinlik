<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ColoringPage;
use Illuminate\Http\Request;

class ColoringPageController extends Controller
{
    public function index()
    {
        return view('admin.pages.index', [
            'pages' => ColoringPage::query()->with('category')->latest()->get(),
            'categoryAssignmentOptions' => Category::orderedFlatWithDepth(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request, true);
        ColoringPage::query()->create($this->uploadAndFormat($request, $data));
        return back()->with('success', 'Boyama sayfası eklendi.');
    }

    public function update(Request $request, ColoringPage $coloringPage)
    {
        $data = $this->validatePayload($request, false);
        $coloringPage->update($this->uploadAndFormat($request, $data));
        return back()->with('success', 'Boyama sayfası güncellendi.');
    }

    public function destroy(ColoringPage $coloringPage)
    {
        $coloringPage->delete();
        return back()->with('success', 'Boyama sayfası silindi.');
    }

    private function validatePayload(Request $request, bool $isCreate): array
    {
        $fileRule = $isCreate
            ? ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:20480']
            : ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:20480'];

        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'shopier_product_url' => ['nullable', 'url', 'max:1000'],
            'is_free' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:8192'],
            'pdf_file' => $fileRule,
        ]);
    }

    private function uploadAndFormat(Request $request, array $data): array
    {
        $isFree = (bool) ($data['is_free'] ?? false);
        $data['is_free'] = $isFree;
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $data['price'] = $isFree ? 0 : ($data['price'] ?? 0);

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')->store('covers', 'public');
        }

        if ($request->hasFile('pdf_file')) {
            $disk = $isFree ? 'public' : 'local';
            $folder = $isFree ? 'free-pages' : 'paid-pages';
            $data['pdf_path'] = $request->file('pdf_file')->store($folder, $disk);
        }

        unset($data['cover_image'], $data['pdf_file']);
        return $data;
    }
}
