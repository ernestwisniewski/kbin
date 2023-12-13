<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Image;

use App\Exception\ImageDownloadTooLargeException;
use League\Flysystem\FilesystemOperator;

class ImageStore
{
    public const MAX_IMAGE_BYTES = 6000000;

    public function __construct(
        private readonly ImageValidate $imageValidate,
        private readonly FilesystemOperator $publicUploadsFilesystem
    ) {
    }

    public function __invoke(string $source, string $filePath): bool
    {
        $fh = fopen($source, 'rb');

        try {
            if (filesize($source) > self::MAX_IMAGE_BYTES) {
                throw new ImageDownloadTooLargeException();
            }

            ($this->imageValidate)($source);

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
}
