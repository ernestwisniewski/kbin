<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Image;

use App\Entity\Image;
use League\Flysystem\FilesystemOperator;

readonly class ImageMimeTypeGet
{
    public const IMAGE_MIMETYPE_STR = 'image/jpeg, image/jpg, image/gif, image/png';

    public function __construct(private FilesystemOperator $publicUploadsFilesystem)
    {
    }

    public function __invoke(Image $image): string
    {
        try {
            return $this->publicUploadsFilesystem->mimeType($image->filePath);
        } catch (\Exception $e) {
            return 'none';
        }
    }
}
