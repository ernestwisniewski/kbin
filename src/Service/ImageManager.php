<?php declare(strict_types=1);

namespace App\Service;

use App\Exception\CorruptedFileException;
use App\Exception\ImageDownloadTooLargeException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use RuntimeException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function count;
use function is_resource;

class ImageManager
{
    const IMAGE_MIMETYPES = ['image/jpeg', 'image/jpg', 'image/gif', 'image/png'];
    const MAX_IMAGE_BYTES = 12000000;

    public function __construct(
        private FilesystemInterface $publicUploadsFilesystem,
        private HttpClientInterface $httpClient,
        private MimeTypesInterface $mimeTypeGuesser,
        private ValidatorInterface $validator,
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

            return $this->publicUploadsFilesystem->has($filePath);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        finally {
            is_resource($fh) and fclose($fh);
        }
    }

    private function validate(string $filePath)
    {
        $violations = $this->validator->validate(
            $filePath,
            [
                new Image(['detectCorrupted' => true]),
            ]
        );

        if (count($violations) > 0) {
            throw new CorruptedFileException();
        }

        return true;
    }

    public function download(string $url): ?string
    {
        $tempFile = @tempnam('/', 'kbin');

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
        } catch (Exception $e) {
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
        return sprintf('%s/%s/%s', substr($this->getFileName($file), 0, 2), substr($this->getFileName($file), 2, 2), $this->getFileName($file));
    }

    public function getFileName(string $file): string
    {
        $hash     = hash_file('sha256', $file);
        $mimeType = $this->mimeTypeGuesser->guessMimeType($file);

        if (!$mimeType) {
            throw new RuntimeException("Couldn't guess MIME type of image");
        }

        $ext = $this->mimeTypeGuesser->getExtensions($mimeType)[0] ?? null;

        if (!$ext) {
            throw new RuntimeException("Couldn't guess extension of image");
        }

        return sprintf('%s.%s', $hash, $ext);
    }

    public function remove(string $path):void
    {
        $this->publicUploadsFilesystem->delete($path);
    }
}
