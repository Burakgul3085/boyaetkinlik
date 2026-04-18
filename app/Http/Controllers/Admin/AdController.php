<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdController extends Controller
{
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
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Reklam alanları güncellendi.');
    }
}
