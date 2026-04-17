<?php

namespace App\Http\Controllers;

use App\Models\ColoringPage;
use App\Models\Transaction;
use App\Support\FileFormatDownloadService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ColoringPageController extends Controller
{
    public function show(ColoringPage $coloringPage, FileFormatDownloadService $downloadService)
    {
        $sourceExtension = $downloadService->sourceExtension($coloringPage->pdf_path);

        return view('frontend.product', [
            'coloringPage' => $coloringPage,
            'downloadFormats' => $downloadService->downloadOptions($sourceExtension),
        ]);
    }

    public function downloadFree(Request $request, ColoringPage $coloringPage, FileFormatDownloadService $downloadService)
    {
        abort_unless($coloringPage->is_free, 403);

        $requestedFormat = $request->string('format')->toString();
        $requestedFormat = $downloadService->normalizeFormat($requestedFormat ?: null);

        if ($requestedFormat !== null && ! in_array($requestedFormat, $downloadService->allowedFormats(), true)) {
            abort(422, 'Geçersiz dosya formatı.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $downloadService->download(
            $disk,
            $coloringPage->pdf_path,
            $coloringPage->title,
            $requestedFormat
        );
    }

    public function printFree(Request $request, ColoringPage $coloringPage, FileFormatDownloadService $downloadService)
    {
        abort_unless($coloringPage->is_free, 403);

        $sourceExtension = $downloadService->sourceExtension($coloringPage->pdf_path);
        $availableFormats = $downloadService->downloadOptions($sourceExtension);

        $requestedFormat = $request->string('format')->toString();
        $requestedFormat = $downloadService->normalizeFormat($requestedFormat ?: $sourceExtension);

        if (! in_array((string) $requestedFormat, $availableFormats, true)) {
            abort(422, 'Geçersiz dosya formatı.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $downloadService->inline(
            $disk,
            $coloringPage->pdf_path,
            $coloringPage->title,
            $requestedFormat
        );
    }

    public function previewImage(ColoringPage $coloringPage): StreamedResponse
    {
        // Güvenlik: Asıl indirilebilir dosyayı (pdf/png/jpg/jpeg) preview endpointinden asla servis etmeyelim.
        // Böylece kullanıcı yalnızca "İndir" akışı üzerinden dosyaya ulaşır.
        if ($coloringPage->cover_image_path) {
            $coverExt = strtolower((string) pathinfo($coloringPage->cover_image_path, PATHINFO_EXTENSION));
            $isImageCover = in_array($coverExt, ['png', 'jpg', 'jpeg'], true);

            /** @var FilesystemAdapter $publicDisk */
            $publicDisk = Storage::disk('public');

            if ($isImageCover && $publicDisk->exists($coloringPage->cover_image_path)) {
                return $publicDisk->response($coloringPage->cover_image_path);
            }
        }

        abort(404);
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
