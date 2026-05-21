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

        if (preg_match('~(?:youtube\.com/watch\?.*v=|youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/)([a-zA-Z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function embedUrl(?string $url): ?string
    {
        $id = static::extractId($url);

        return $id !== null
            ? 'https://www.youtube-nocookie.com/embed/'.$id.'?rel=0&modestbranding=1'
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
