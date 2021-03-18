<?php declare(strict_types=1);

namespace App\Service;

use App\Exception\CorruptedFileException;
use App\Exception\ImageDownloadTooLargeException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Mime\MimeTypesInterface;
use League\Flysystem\FilesystemInterface;

class ImageManager
{
    const IMAGE_MIMETYPES = ['image/jpeg', 'image/gif', 'image/png'];
    const MAX_IMAGE_BYTES = 12000000;

    private FilesystemInterface $defaultStorage;
    private HttpClientInterface $httpClient;
    private MimeTypesInterface $mimeTypeGuesser;
    private ValidatorInterface $validator;

    public function __construct(
        FilesystemInterface $publicUploadsFilesystem,
        HttpClientInterface $httpClient,
        MimeTypesInterface $mimeTypeGuesser,
        ValidatorInterface $validator
    ) {
        $this->defaultStorage  = $publicUploadsFilesystem;
        $this->httpClient      = $httpClient;
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->validator       = $validator;
    }

    public function store(string $source, string $filePath): bool
    {
        $fh = fopen($source, 'rb');

        try {
            if (filesize($source) > self::MAX_IMAGE_BYTES) {
                throw new ImageDownloadTooLargeException();
            }
            $this->validate($source);

            $this->defaultStorage->writeStream($filePath, $fh);

            return $this->defaultStorage->has($filePath);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        finally {
            \is_resource($fh) and fclose($fh);
        }
    }

    public function download(string $url): ?string
    {
        $tempFile = @tempnam(sys_get_temp_dir(), 'pml');

        if ($tempFile === false) {
            throw new UnrecoverableMessageHandlingException('Couldn\'t create temporary file');
        }

        $fh = fopen($tempFile, 'wb');

        try {
            $response = $this->httpClient->request(
                'GET',
                $url,
                [
                    'headers'     => [
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
            fclose($fh);
            unlink($tempFile);

            return null;
        }

        return $tempFile;
    }

    public function getFilePath(string $file): string
    {
        return sprintf('%s/%s/%s', $this->getRandomCharacters(), $this->getRandomCharacters(), $this->getFileName($file));
    }

    public function getFileName(string $file): string
    {
        $hash     = hash_file('sha256', $file);
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

    private function getRandomCharacters($length = 2): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle($chars), 0, $length);
    }

    private function validate(string $filePath)
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

    public function isImageUrl(string $url): bool
    {
        $urlExt = pathinfo($url, PATHINFO_EXTENSION);
        $types  = array_map(
            function ($type) {
                return str_replace('image/', '', $type);
            },
            self::IMAGE_MIMETYPES
        );

        if (in_array($urlExt, $types)) {
            return true;
        }

        return false;
    }
}
