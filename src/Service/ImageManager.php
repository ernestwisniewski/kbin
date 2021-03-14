<?php declare(strict_types = 1);

namespace App\Service;

use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Mime\MimeTypesInterface;
use League\Flysystem\FilesystemInterface;

class ImageManager
{
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
            $this->validate($source);
            $this->defaultStorage->writeStream($filePath, $fh);

            return $this->defaultStorage->has($filePath);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        } finally {
            \is_resource($fh) and fclose($fh);
        }
    }

    public function download(string $url): ?string
    {
        $tempFile = @tempnam(sys_get_temp_dir(), 'pml');

        if ($tempFile === false) {
            throw new UnrecoverableMessageHandlingException('Couldn\'t create temporary file');
        }

        try {
            $response = $this->httpClient->request(
                'GET',
                $url,
                [
                    'headers' => [
                        'Accept' => 'image/jpeg, image/gif, image/png',
                    ],
                ]
            );

            $fh = fopen($tempFile, 'wb');
            foreach ($this->httpClient->stream($response) as $chunk) {
                fwrite($fh, $chunk->getContent());
            }
            fclose($fh);

            return $tempFile;
        } catch (\Exception $e) {
            return null;
        }
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
            return false;
        }

        return true;
    }
}
