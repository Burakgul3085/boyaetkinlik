<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function paid(string $token)
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

        abort_if($transaction->downloaded_at !== null, 403, 'Bu indirme linki daha önce kullanıldı.');

        $transaction->update(['downloaded_at' => now()]);

        $downloadPath = $transaction->coloringPage->pdf_path;
        $extension = pathinfo($downloadPath, PATHINFO_EXTENSION);
        $extension = $extension ? '.'.strtolower($extension) : '.pdf';

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        return $disk->download(
            $downloadPath,
            $transaction->coloringPage->title.$extension
        );
    }
}
