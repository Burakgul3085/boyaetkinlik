<?php

namespace App\Support;

class YoutubeEmbed
{
    public static function extractId(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/|m\.youtube\.com/watch\?(?:.*&)?v=)([a-zA-Z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }

        if (preg_match('~[?&]v=([a-zA-Z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function embedUrl(?string $url): ?string
    {
        $id = static::extractId($url);

        return $id !== null
            ? 'https://www.youtube.com/embed/'.$id.'?rel=0&modestbranding=1&playsinline=1'
            : null;
    }

    public static function thumbnailUrl(?string $url): ?string
    {
        $id = static::extractId($url);

        return $id !== null
            ? 'https://img.youtube.com/vi/'.$id.'/hqdefault.jpg'
            : null;
    }
}
