<?php

namespace App\Http\Controllers;

use App\Models\ColoringPage;
use App\Models\Transaction;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ColoringPageController extends Controller
{
    public function show(ColoringPage $coloringPage)
    {
        return view('frontend.product', compact('coloringPage'));
    }

    public function downloadFree(ColoringPage $coloringPage)
    {
        abort_unless($coloringPage->is_free, 403);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->download(
            $coloringPage->pdf_path,
            $this->resolveDownloadFileName($coloringPage)
        );
    }

    public function previewImage(ColoringPage $coloringPage): StreamedResponse
    {
        if ($coloringPage->cover_image_path) {
            /** @var FilesystemAdapter $publicDisk */
            $publicDisk = Storage::disk('public');

            if ($publicDisk->exists($coloringPage->cover_image_path)) {
                return $publicDisk->response($coloringPage->cover_image_path);
            }
        }

        $extension = strtolower((string) pathinfo($coloringPage->pdf_path, PATHINFO_EXTENSION));
        $imageExtensions = ['png', 'jpg', 'jpeg'];
        abort_unless(in_array($extension, $imageExtensions, true), 404);

        $fileDisk = $coloringPage->is_free ? 'public' : 'local';
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($fileDisk);
        abort_unless($disk->exists($coloringPage->pdf_path), 404);

        return $disk->response($coloringPage->pdf_path);
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

    private function resolveDownloadFileName(ColoringPage $coloringPage): string
    {
        $extension = pathinfo($coloringPage->pdf_path, PATHINFO_EXTENSION);
        $extension = $extension ? '.'.strtolower($extension) : '.pdf';

        return $coloringPage->title.$extension;
    }
}
