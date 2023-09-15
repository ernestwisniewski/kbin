<?php

declare(strict_types=1);

namespace App\Service;

class VideoManager
{
    public const VIDEO_MIMETYPES = ['video/mp4', 'video/webm'];

    public static function isVideoUrl(string $url): bool
    {
        $urlExt = pathinfo($url, PATHINFO_EXTENSION);

        $types = array_map(fn ($type) => str_replace('video/', '', $type), self::VIDEO_MIMETYPES);

        return \in_array($urlExt, $types);
    }
}
