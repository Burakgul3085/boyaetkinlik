<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Support\FileFormatDownloadService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

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
        $mainRelativePath = $transaction->coloringPage->mainDownloadRelativePath();
        $sourceExtension = $downloadService->sourceExtension($mainRelativePath);
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

        if ($transaction->coloringPage->mainFilePathLooksLikeCoverFolder()) {
            return redirect()
                ->route('download.paid', ['token' => $token])
                ->with('download_error', 'Ana dosya yolu hatalı görünüyor (kapak klasörü). Yönetim panelinde «Dosya (PDF/PNG/…)» alanına asıl indirilecek dosyayı yeniden yükleyin.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = $transaction->coloringPage->diskForMainFile();

        try {
            $response = $downloadService->download(
                $disk,
                $mainRelativePath,
                $transaction->coloringPage->title,
                $requestedFormat
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('download.paid', ['token' => $token])
                ->with('download_error', 'Dosya hazırlanırken bir sorun oluştu. Lütfen farklı bir format deneyin veya daha sonra tekrar deneyin.');
        }

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

        $mainRelativePath = $transaction->coloringPage->mainDownloadRelativePath();
        $sourceExtension = $downloadService->sourceExtension($mainRelativePath);
        $downloadFormats = $downloadService->downloadOptions($sourceExtension);

        $requestedFormat = $request->string('format')->toString();
        $requestedFormat = $downloadService->normalizeFormat($requestedFormat ?: $sourceExtension);

        if (! in_array((string) $requestedFormat, $downloadFormats, true)) {
            abort(422, 'Geçersiz dosya formatı.');
        }

        if ($transaction->coloringPage->mainFilePathLooksLikeCoverFolder()) {
            return redirect()
                ->route('download.paid', ['token' => $token])
                ->with('download_error', 'Ana dosya yolu hatalı görünüyor (kapak klasörü). Yönetim panelinde «Dosya (PDF/PNG/…)» alanına asıl indirilecek dosyayı yeniden yükleyin.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = $transaction->coloringPage->diskForMainFile();

        try {
            $response = $downloadService->inline(
                $disk,
                $mainRelativePath,
                $transaction->coloringPage->title,
                $requestedFormat
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('download.paid', ['token' => $token])
                ->with('download_error', 'Yazdırılabilir dosya hazırlanırken bir sorun oluştu. Lütfen farklı bir format deneyin veya daha sonra tekrar deneyin.');
        }

        $transaction->update([
            'downloaded_at' => $transaction->downloaded_at ?? now(),
        ]);

        return $response;
    }
}
