<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\CorruptedFileException;
use App\Exception\ImageDownloadTooLargeException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImageManager
{
    public const IMAGE_MIMETYPES = ['image/jpeg', 'image/jpg', 'image/gif', 'image/png'];
    public const MAX_IMAGE_BYTES = 6000000;

    public function __construct(
        private readonly string $storageUrl,
        private readonly FilesystemOperator $publicUploadsFilesystem,
        private readonly HttpClientInterface $httpClient,
        private readonly MimeTypesInterface $mimeTypeGuesser,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public static function isImageUrl(string $url): bool
    {
        $urlExt = pathinfo($url, PATHINFO_EXTENSION);

        $types = array_map(fn($type) => str_replace('image/', '', $type), self::IMAGE_MIMETYPES);

        return in_array($urlExt, $types);
    }

    public function store(string $source, string $filePath): bool
    {
        $fh = fopen($source, 'rb');

        try {
            if (filesize($source) > self::MAX_IMAGE_BYTES) {
                throw new ImageDownloadTooLargeException();
            }

            $this->validate($source);

            $this->publicUploadsFilesystem->writeStream($filePath, $fh);

            if (!$this->publicUploadsFilesystem->has($filePath)) {
                throw new \Exception('File not found');
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        } finally {
            \is_resource($fh) and fclose($fh);
        }
    }

    private function validate(string $filePath): bool
    {
        $violations = $this->validator->validate(
            $filePath,
            [
                new Image(['detectCorrupted' => true]),
            ]
        );

        if (\count($violations) > 0) {
            throw new CorruptedFileException();
        }

        return true;
    }

    public function download(string $url): ?string
    {
        $tempFile = @tempnam('/', 'kbin');

        if (false === $tempFile) {
            throw new UnrecoverableMessageHandlingException('Couldn\'t create temporary file');
        }

        $fh = fopen($tempFile, 'wb');

        try {
            $response = $this->httpClient->request(
                'GET',
                $url,
                [
                    'timeout' => 5,
                    'max_duration' => 5,
                    'headers' => [
                        'Accept' => implode(', ', self::IMAGE_MIMETYPES),
                    ],
                    'on_progress' => function (int $downloaded, int $downloadSize) {
                        if (
                            $downloaded > self::MAX_IMAGE_BYTES
                            || $downloadSize > self::MAX_IMAGE_BYTES
                        ) {
                            throw new ImageDownloadTooLargeException();
                        }
                    },
                ]
            );

            foreach ($this->httpClient->stream($response) as $chunk) {
                fwrite($fh, $chunk->getContent());
            }

            fclose($fh);

            $this->validate($tempFile);
        } catch (\Exception $e) {
            if ($fh) {
                fclose($fh);
            }
            unlink($tempFile);

            return null;
        }

        return $tempFile;
    }

    public function getFilePath(string $file): string
    {
        return sprintf(
            '%s/%s/%s',
            substr($this->getFileName($file), 0, 2),
            substr($this->getFileName($file), 2, 2),
            $this->getFileName($file)
        );
    }

    public function getFileName(string $file): string
    {
        $hash = hash_file('sha256', $file);
        $mimeType = $this->mimeTypeGuesser->guessMimeType($file);

        if (!$mimeType) {
            throw new \RuntimeException("Couldn't guess MIME type of image");
        }

        $ext = $this->mimeTypeGuesser->getExtensions($mimeType)[0] ?? null;

        if (!$ext) {
            throw new \RuntimeException("Couldn't guess extension of image");
        }

        return sprintf('%s.%s', $hash, $ext);
    }

    public function remove(string $path): void
    {
        $this->publicUploadsFilesystem->delete($path);
    }

    public function getPath(\App\Entity\Image $image): string
    {
        return $this->publicUploadsFilesystem->read($image->filePath);
    }

    public function getUrl(?\App\Entity\Image $image): ?string
    {
        if (!$image) {
            return null;
        }

        return $this->storageUrl.'/'.$image->filePath;
    }

    public function getMimetype(\App\Entity\Image $image): string
    {
        try {
            return $this->publicUploadsFilesystem->mimeType($image->filePath);
        } catch (\Exception $e) {
            return 'none';
        }
    }
}
