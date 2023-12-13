<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Image;

use App\Exception\ImageDownloadTooLargeException;
use App\Kbin\Image\Enum\ImageMimeType;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class ImageDownload
{
    public function __construct(
        private ImageValidate $imageValidate,
        private HttpClientInterface $httpClient,
    ) {
    }

    public function __invoke(string $url): ?string
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
                        'Accept' => implode(', ', ImageMimeType::getAllValues()),
                    ],
                    'on_progress' => function (int $downloaded, int $downloadSize) {
                        if (
                            $downloaded > ImageStore::MAX_IMAGE_BYTES
                            || $downloadSize > ImageStore::MAX_IMAGE_BYTES
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

            ($this->imageValidate)($tempFile);
        } catch (\Exception $e) {
            if ($fh) {
                fclose($fh);
            }
            unlink($tempFile);

            return null;
        }

        return $tempFile;
    }
}
