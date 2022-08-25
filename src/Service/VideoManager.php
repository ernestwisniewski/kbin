<?php declare(strict_types=1);

namespace App\Service;

use App\Exception\CorruptedFileException;
use App\Exception\ImageDownloadTooLargeException;
use Exception;
use League\Flysystem\FilesystemOperator;
use RuntimeException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function count;
use function is_resource;

class VideoManager
{
    const VIDEO_MIMETYPES = ['video/mp4', 'video/webm'];

    public static function isVideoUrl(string $url): bool
    {
        $urlExt = pathinfo($url, PATHINFO_EXTENSION);

        $types = array_map(fn($type) => str_replace('video/', '', $type), self::VIDEO_MIMETYPES);

        return in_array($urlExt, $types);
    }
}
