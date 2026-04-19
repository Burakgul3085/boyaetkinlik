<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Support\FileFormatDownloadService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function paid(Request $request, string $token, FileFormatDownloadService $downloadService)
    {
        $transaction = Transaction::query()
            ->where('download_token', $token)
            ->where('status', 'paid')
            ->firstOrFail();

        abort_if(
            $transaction->token_expires_at && Carbon::now()->greaterThan($transaction->token_expires_at),
            403,
            'İndirme linkinin süresi doldu.'
        );

        $requestedFormat = $request->string('format')->toString();
        $requestedFormat = $downloadService->normalizeFormat($requestedFormat ?: null);
        $sourceExtension = $downloadService->sourceExtension($transaction->coloringPage->pdf_path);
        $downloadFormats = $downloadService->downloadOptions($sourceExtension);

        if ($requestedFormat === null) {
            return view('frontend.download-options', [
                'transaction' => $transaction,
                'downloadFormats' => $downloadFormats,
            ]);
        }

        if (! in_array($requestedFormat, $downloadFormats, true)) {
            abort(422, 'Geçersiz dosya formatı.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        $response = $downloadService->download(
            $disk,
            $transaction->coloringPage->pdf_path,
            $transaction->coloringPage->title,
            $requestedFormat
        );

        $transaction->update([
            'downloaded_at' => $transaction->downloaded_at ?? now(),
        ]);

        return $response;
    }

    public function printPaid(Request $request, string $token, FileFormatDownloadService $downloadService)
    {
        $transaction = Transaction::query()
            ->where('download_token', $token)
            ->where('status', 'paid')
            ->firstOrFail();

        abort_if(
            $transaction->token_expires_at && Carbon::now()->greaterThan($transaction->token_expires_at),
            403,
            'İndirme linkinin süresi doldu.'
        );

        $sourceExtension = $downloadService->sourceExtension($transaction->coloringPage->pdf_path);
        $downloadFormats = $downloadService->downloadOptions($sourceExtension);

        $requestedFormat = $request->string('format')->toString();
        $requestedFormat = $downloadService->normalizeFormat($requestedFormat ?: $sourceExtension);

        if (! in_array((string) $requestedFormat, $downloadFormats, true)) {
            abort(422, 'Geçersiz dosya formatı.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        $response = $downloadService->inline(
            $disk,
            $transaction->coloringPage->pdf_path,
            $transaction->coloringPage->title,
            $requestedFormat
        );

        $transaction->update([
            'downloaded_at' => $transaction->downloaded_at ?? now(),
        ]);

        return $response;
    }
}
