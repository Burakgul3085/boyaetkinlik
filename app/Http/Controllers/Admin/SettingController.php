<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'header_site_name' => ['nullable', 'string', 'max:120'],
            'about' => ['nullable', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_address' => ['nullable', 'string', 'max:500'],
            'map_embed_url' => ['nullable', 'url', 'max:1000'],
            'social_tiktok_url' => ['nullable', 'url', 'max:1000'],
            'social_instagram_url' => ['nullable', 'url', 'max:1000'],
            'social_youtube_url' => ['nullable', 'url', 'max:1000'],
            'social_pinterest_url' => ['nullable', 'url', 'max:1000'],
            'social_dailymotion_url' => ['nullable', 'url', 'max:1000'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl'],
            'smtp_from_email' => ['nullable', 'email', 'max:255'],
            'smtp_from_name' => ['nullable', 'string', 'max:255'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string'],
            'navbar_links' => ['nullable', 'string'],
            'shopier_api_key' => ['nullable', 'string'],
            'shopier_api_secret' => ['nullable', 'string'],
            'shopier_website_index' => ['nullable', 'string'],
            'shopier_endpoint' => ['nullable', 'string'],
            'site_logo' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png', 'max:4096'],
        ]);

        unset($data['site_logo']);

        if ($request->hasFile('site_logo')) {
            $previous = Setting::getValue('site_logo', '');
            if (is_string($previous) && $previous !== '' && Storage::disk('public')->exists($previous)) {
                Storage::disk('public')->delete($previous);
            }
            $storedPath = $request->file('site_logo')->store('branding', 'public');
            Setting::query()->updateOrCreate(['key' => 'site_logo'], ['value' => $storedPath]);
        }

        foreach ($data as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Ayarlar kaydedildi.');
    }
}
