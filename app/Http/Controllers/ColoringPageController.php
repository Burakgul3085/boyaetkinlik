<?php

namespace App\Http\Controllers;

use App\Models\ColoringPage;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ColoringPageController extends Controller
{
    public function show(ColoringPage $coloringPage)
    {
        return view('frontend.product', compact('coloringPage'));
    }

    public function downloadFree(ColoringPage $coloringPage)
    {
        abort_unless($coloringPage->is_free, 403);

        return Storage::disk('public')->download($coloringPage->pdf_path, $coloringPage->title.'.pdf');
    }

    public function buy(Request $request, ColoringPage $coloringPage)
    {
        abort_if($coloringPage->is_free, 403);

        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $transaction = Transaction::query()->create([
            'coloring_page_id' => $coloringPage->id,
            'email' => $data['email'],
            'paid_amount' => $coloringPage->price,
            'status' => 'pending',
        ]);

        // Gerçek Shopier entegrasyonu için gerekli parametreler .env üzerinden doldurulmalıdır.
        return redirect()->route('shopier.redirect', $transaction);
    }
}
