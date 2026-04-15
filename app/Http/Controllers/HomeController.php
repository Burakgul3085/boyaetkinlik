<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ColoringPage;
use App\Models\Setting;

class HomeController extends Controller
{
    public function __invoke()
    {
        return view('frontend.home', [
            'categories' => Category::query()->latest()->get(),
            'featuredPages' => ColoringPage::query()->latest()->take(8)->get(),
            'adsHeader' => Setting::getValue('ads_header'),
            'adsLeft' => Setting::getValue('ads_left'),
            'adsRight' => Setting::getValue('ads_right'),
        ]);
    }
}
