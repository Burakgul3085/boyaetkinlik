<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PrivateGiftController extends Controller
{
    public function show(Request $request): Response
    {
        $this->assertGiftAccess();

        $config = config('private-gift');
        $photoExists = $this->photoAbsolutePath() !== null;
        $music = $config['music'] ?? [];

        return response()
            ->view('private.gift', [
                'pageTitle' => (string) ($config['page_title'] ?? 'Biraz Gülümse'),
                'sender' => (string) ($config['sender'] ?? 'Burak'),
                'music' => $music,
                'photoUrl' => $photoExists
                    ? route('private-gift.photo')
                    : null,
                'youtube' => $this->youtubePayload($music['primary'] ?? []),
            ])
            ->withHeaders($this->noStoreHeaders());
    }

    public function photo(Request $request): BinaryFileResponse
    {
        $this->assertGiftAccess();

        $path = $this->photoAbsolutePath();
        if ($path === null) {
            abort(404);
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            default => 'application/octet-stream',
        };

        return response()->file($path, array_merge($this->noStoreHeaders(), [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
        ]));
    }

    private function assertGiftAccess(): void
    {
        if (! config('private-gift.enabled')) {
            abort(404);
        }

        $path = trim((string) config('private-gift.path', ''), '/');
        if ($path === '' || ! preg_match('/^[A-Za-z0-9\-_]+$/', $path)) {
            abort(404);
        }
    }

    /**
     * @param  array<string, mixed>  $primary
     * @return array{id: string, start: int, embed_url: string}|null
     */
    private function youtubePayload(array $primary): ?array
    {
        $id = trim((string) ($primary['youtube_id'] ?? ''));
        $url = trim((string) ($primary['url'] ?? ''));

        if ($id === '' && $url !== '') {
            $id = $this->extractYoutubeId($url) ?? '';
        }

        if ($id === '' || ! preg_match('/^[A-Za-z0-9_\-]{6,20}$/', $id)) {
            return null;
        }

        $start = max(0, (int) ($primary['start_seconds'] ?? 0));
        $query = http_build_query([
            'start' => $start,
            'autoplay' => 1,
            'rel' => 0,
            'modestbranding' => 1,
            'playsinline' => 1,
            'enablejsapi' => 1,
        ]);

        return [
            'id' => $id,
            'start' => $start,
            'embed_url' => 'https://www.youtube.com/embed/'.$id.'?'.$query,
        ];
    }

    private function extractYoutubeId(string $url): ?string
    {
        if (preg_match('~(?:youtu\.be/|v=|embed/)([A-Za-z0-9_\-]{6,20})~', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    private function photoAbsolutePath(): ?string
    {
        $relative = (string) config('private-gift.photo_relative', 'private/gift/profile.jpg');
        $relative = ltrim(str_replace(['\\', '..'], ['/', ''], $relative), '/');
        $path = storage_path('app/'.$relative);

        if (! is_file($path) || ! is_readable($path)) {
            // Fallback common extensions
            $base = storage_path('app/private/gift/profile');
            foreach (['.jpg', '.jpeg', '.png', '.webp'] as $ext) {
                $try = $base.$ext;
                if (is_file($try) && is_readable($try)) {
                    $path = $try;
                    break;
                }
            }
        }

        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $real = realpath($path);
        $root = realpath(storage_path('app'));
        if ($real === false || $root === false || ! str_starts_with($real, $root)) {
            return null;
        }

        return $real;
    }

    /**
     * @return array<string, string>
     */
    private function noStoreHeaders(): array
    {
        return [
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive, nosnippet',
        ];
    }
}
