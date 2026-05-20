<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileFormatDownloadService
{
    /** @var string[] */
    private array $allowedFormats = ['pdf', 'jpg', 'jpeg', 'png'];

    public function allowedFormats(): array
    {
        return $this->allowedFormats;
    }

    public function normalizeFormat(?string $format): ?string
    {
        if ($format === null || $format === '') {
            return null;
        }

        $format = strtolower(trim($format));

        return $format;
    }

    public function sourceExtension(string $path): string
    {
        return strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * @return string[]
     */
    public function downloadOptions(string $sourceExtension): array
    {
        $sourceExtension = strtolower($sourceExtension);

        $options = [$sourceExtension, 'pdf', 'jpg', 'png', 'jpeg'];

        return collect($options)
            ->filter(fn ($item) => $this->canConvert($sourceExtension, $item))
            ->unique()
            ->values()
            ->all();
    }

    public function download(FilesystemAdapter $disk, string $path, string $baseName, ?string $requestedFormat): StreamedResponse|BinaryFileResponse
    {
        $resolved = $this->resolveFileForFormat($disk, $path, $requestedFormat);
        $filename = $this->safeBaseName($baseName).'.'.$resolved['extension'];

        if (! $resolved['is_temporary']) {
            return $disk->download($path, $filename);
        }

        return response()->download($resolved['absolute_path'], $filename)->deleteFileAfterSend(true);
    }

    /**
     * E-posta eki veya gönderim öncesi tek dosya yolu (geçici dönüştürülen dosyalar sonra silinir).
     *
     * @return array{path: string, filename: string, delete_after_send: bool}
     */
    public function prepareAttachment(FilesystemAdapter $disk, string $path, string $baseName, ?string $requestedFormat): array
    {
        $format = $this->normalizeFormat($requestedFormat);
        $resolved = $this->resolveFileForFormat($disk, $path, $format);

        return [
            'path' => $resolved['absolute_path'],
            'filename' => $this->safeBaseName($baseName).'.'.$resolved['extension'],
            'delete_after_send' => $resolved['is_temporary'],
        ];
    }

    /**
     * Online boya: ana dosyayı (pdf/png/jpg) raster PNG yoluna çevirir.
     *
     * @return array{absolute_path: string, is_temporary: bool}
     */
    public function lineArtRasterForPainting(FilesystemAdapter $disk, string $path): array
    {
        $sourceExt = $this->sourceExtension($path);

        if (in_array($sourceExt, ['png', 'jpg', 'jpeg'], true)) {
            $absolute = $disk->path($path);
            if (! is_file($absolute)) {
                throw new RuntimeException('Boyama dosyası bulunamadı.');
            }

            return [
                'absolute_path' => $absolute,
                'is_temporary' => false,
            ];
        }

        if ($sourceExt === 'pdf') {
            if (extension_loaded('imagick')) {
                $png = $this->convertPdfFirstPageWithImagick($disk->path($path));
                if ($png !== null) {
                    return [
                        'absolute_path' => $png,
                        'is_temporary' => true,
                    ];
                }
            }

            $resolved = $this->resolveFileForFormat($disk, $path, 'png');

            return [
                'absolute_path' => $resolved['absolute_path'],
                'is_temporary' => $resolved['is_temporary'],
            ];
        }

        throw new RuntimeException('Bu dosya türü online boya için desteklenmiyor.');
    }

    /**
     * İstemciden gelen boyanmış PNG’yi istenen formata hazırlar.
     *
     * @return array{path: string, filename: string, delete_after_send: bool, extension: string}
     */
    public function preparePaintedExport(string $pngAbsolutePath, string $baseName, ?string $requestedFormat): array
    {
        $format = $this->normalizeFormat($requestedFormat) ?: 'png';
        if (! in_array($format, $this->allowedFormats, true)) {
            throw new RuntimeException('Geçersiz format.');
        }

        if ($format === 'png') {
            return [
                'path' => $pngAbsolutePath,
                'filename' => $this->safeBaseName($baseName).'-boyanmis.png',
                'delete_after_send' => false,
                'extension' => 'png',
            ];
        }

        if (in_array($format, ['jpg', 'jpeg'], true)) {
            $jpgPath = $this->convertRasterToJpeg($pngAbsolutePath);

            return [
                'path' => $jpgPath,
                'filename' => $this->safeBaseName($baseName).'-boyanmis.jpg',
                'delete_after_send' => true,
                'extension' => 'jpg',
            ];
        }

        $pdfPath = $this->convertImageToPdf($pngAbsolutePath);

        return [
            'path' => $pdfPath,
            'filename' => $this->safeBaseName($baseName).'-boyanmis.pdf',
            'delete_after_send' => true,
            'extension' => 'pdf',
        ];
    }

    public function inline(FilesystemAdapter $disk, string $path, string $baseName, ?string $requestedFormat): BinaryFileResponse
    {
        $resolved = $this->resolveFileForFormat($disk, $path, $requestedFormat);
        $filename = $this->safeBaseName($baseName).'.'.$resolved['extension'];

        $headers = [
            'Content-Type' => $this->mimeTypeForExtension($resolved['extension']),
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ];

        if ($resolved['is_temporary']) {
            register_shutdown_function(static function () use ($resolved): void {
                @unlink($resolved['absolute_path']);
            });
        }

        return response()->file($resolved['absolute_path'], $headers);
    }

    private function convertPdfFirstPageWithImagick(string $pdfAbsolutePath): ?string
    {
        try {
            $imagick = new \Imagick();
            $imagick->setResolution(144, 144);
            $imagick->readImage($pdfAbsolutePath.'[0]');
            $imagick->setImageBackgroundColor('white');
            $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            $imagick->setImageFormat('png');

            $tmpDir = storage_path('app/tmp-converted');
            if (! is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }

            $target = $tmpDir.'/'.Str::uuid().'.png';
            $imagick->writeImage($target);
            $imagick->clear();
            $imagick->destroy();

            return is_file($target) ? $target : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function convertRasterToJpeg(string $pngAbsolutePath): string
    {
        $tmpDir = storage_path('app/tmp-converted');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $target = $tmpDir.'/'.Str::uuid().'.jpg';

        if (extension_loaded('imagick')) {
            $img = new \Imagick($pngAbsolutePath);
            $img->setImageFormat('jpeg');
            $img->setImageCompressionQuality(92);
            $img->writeImage($target);
            $img->clear();
            $img->destroy();

            return $target;
        }

        $image = imagecreatefrompng($pngAbsolutePath);
        if ($image === false) {
            throw new RuntimeException('Görsel işlenemedi.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $canvas = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
        imagejpeg($canvas, $target, 92);
        imagedestroy($image);
        imagedestroy($canvas);

        return $target;
    }

    private function convertImageToPdf(string $sourceAbsolutePath): string
    {
        $tmpDir = storage_path('app/tmp-converted');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $targetPdfPath = $tmpDir.'/'.Str::uuid().'.pdf';

        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetMargins(0, 0);
        $pdf->Image($sourceAbsolutePath, 10, 10, 190);
        $pdf->Output('F', $targetPdfPath);

        return $targetPdfPath;
    }

    private function convertByLibreOffice(string $sourceAbsolutePath, string $targetExt): ?string
    {
        $tmpDir = storage_path('app/tmp-converted/'.Str::uuid()->toString());
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $convertTo = $targetExt === 'jpeg' ? 'jpg' : $targetExt;

        $process = new Process([
            'soffice',
            '--headless',
            '--convert-to',
            $convertTo,
            '--outdir',
            $tmpDir,
            $sourceAbsolutePath,
        ]);

        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        $filenameWithoutExt = pathinfo($sourceAbsolutePath, PATHINFO_FILENAME);
        $convertedPath = $tmpDir.'/'.$filenameWithoutExt.'.'.$convertTo;

        if (! is_file($convertedPath)) {
            $matches = glob($tmpDir.'/*.'.$convertTo);
            $convertedPath = $matches[0] ?? null;
        }

        return $convertedPath;
    }

    private function canConvert(string $sourceExt, string $targetExt): bool
    {
        $sourceExt = strtolower($sourceExt);
        $targetExt = strtolower($targetExt);

        if (! in_array($sourceExt, $this->allowedFormats, true) || ! in_array($targetExt, $this->allowedFormats, true)) {
            return false;
        }

        if ($sourceExt === $targetExt) {
            return true;
        }

        if (in_array($sourceExt, ['jpg', 'jpeg', 'png'], true) && $targetExt === 'pdf') {
            return true;
        }

        return $this->isSofficeAvailable();
    }

    private function isSofficeAvailable(): bool
    {
        $process = new Process(['soffice', '--version']);
        $process->setTimeout(5);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * @return array{absolute_path: string, extension: string, is_temporary: bool}
     */
    private function resolveFileForFormat(FilesystemAdapter $disk, string $path, ?string $requestedFormat): array
    {
        $sourceExt = $this->sourceExtension($path);
        $targetExt = $this->normalizeFormat($requestedFormat) ?: $sourceExt;

        if (! in_array($targetExt, $this->allowedFormats, true)) {
            throw new RuntimeException('Desteklenmeyen indirme formatı.');
        }

        $sourceAbsolutePath = $disk->path($path);
        if (! is_file($sourceAbsolutePath)) {
            throw new RuntimeException('İndirilecek kaynak dosya bulunamadı.');
        }

        if ($targetExt === $sourceExt) {
            return [
                'absolute_path' => $sourceAbsolutePath,
                'extension' => $sourceExt,
                'is_temporary' => false,
            ];
        }

        if (in_array($sourceExt, ['jpg', 'jpeg', 'png'], true) && $targetExt === 'pdf') {
            return [
                'absolute_path' => $this->convertImageToPdf($sourceAbsolutePath),
                'extension' => 'pdf',
                'is_temporary' => true,
            ];
        }

        $convertedPath = $this->convertByLibreOffice($sourceAbsolutePath, $targetExt);
        if ($convertedPath === null || ! is_file($convertedPath)) {
            throw new RuntimeException('Dönüşüm şu an desteklenmiyor. Farklı format seçin.');
        }

        return [
            'absolute_path' => $convertedPath,
            'extension' => $targetExt,
            'is_temporary' => true,
        ];
    }

    private function mimeTypeForExtension(string $extension): string
    {
        return match (strtolower($extension)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/pdf',
        };
    }

    private function safeBaseName(string $baseName): string
    {
        $clean = preg_replace('/[^\w\-\.]+/u', '_', trim($baseName));

        return $clean !== null && $clean !== '' ? $clean : 'dosya';
    }
}
