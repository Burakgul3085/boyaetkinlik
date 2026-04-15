<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'settings' => Setting::query()->pluck('value', 'key'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'about' => ['nullable', 'string'],
            'contact' => ['nullable', 'string'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string'],
            'navbar_links' => ['nullable', 'string'],
            'shopier_api_key' => ['nullable', 'string'],
            'shopier_api_secret' => ['nullable', 'string'],
            'shopier_website_index' => ['nullable', 'string'],
            'shopier_endpoint' => ['nullable', 'string'],
        ]);

        foreach ($data as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Ayarlar kaydedildi.');
    }
}
