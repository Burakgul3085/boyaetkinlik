<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdController extends Controller
{
    /**
     * Tekrarlanan adsbygoogle.js scriptlerini alan kodlarından temizler.
     */
    private function normalizeAdMarkup(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace(
            [
                '/<script[^>]*src=["\']https:\/\/pagead2\.googlesyndication\.com\/pagead\/js\/adsbygoogle\.js[^"\']*["\'][^>]*>\s*<\/script>/i',
                '/<script\b[^>]*>\s*\(adsbygoogle\s*=\s*window\.adsbygoogle\s*\|\|\s*\[\]\)\.push\(\s*(?:\{[\s\S]*?\})?\s*\)\s*;?\s*<\/script>/i',
            ],
            '',
            $value
        );

        return is_string($normalized) ? trim($normalized) : trim($value);
    }

    public function index()
    {
        return view('admin.ads.index', [
            'settings' => Setting::query()->pluck('value', 'key'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'ads_header' => ['nullable', 'string', 'max:100000'],
            'ads_left' => ['nullable', 'string', 'max:100000'],
            'ads_right' => ['nullable', 'string', 'max:100000'],
            'ads_product_detail' => ['nullable', 'string', 'max:100000'],
            'ads_footer' => ['nullable', 'string', 'max:100000'],
        ]);

        foreach ($data as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $this->normalizeAdMarkup($value)]);
        }

        return back()->with('success', 'Reklam alanları güncellendi.');
    }
}
