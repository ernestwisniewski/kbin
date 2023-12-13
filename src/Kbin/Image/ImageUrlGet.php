<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Image;

use App\Entity\Image;

readonly class ImageUrlGet
{
    public function __construct(private string $storageUrl)
    {
    }

    public function __invoke(?Image $image): ?string
    {
        if (!$image) {
            return null;
        }

        return $this->storageUrl.'/'.$image->filePath;
    }
}
