<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service\ActivityPub\Wrapper;

use App\Entity\Image;
use App\Kbin\Image\ImageMimeTypeGet;
use App\Kbin\Image\ImageUrlGet;

class ImageWrapper
{
    public function __construct(
        private readonly ImageMimeTypeGet $imageMimeTypeGet,
        private readonly ImageUrlGet $imageUrlGet
    ) {
    }

    public function build(array $item, Image $image, string $title = ''): array
    {
        $item['attachment'][] = [
            'type' => 'Image',
            'mediaType' => ($this->imageMimeTypeGet)($image),
            'url' => ($this->imageUrlGet)($image),
            'name' => $image->altText,
            'blurhash' => $image->blurhash,
            'focalPoint' => [0, 0],
            'width' => $image->width,
            'height' => $image->height,
        ];

        $item['image'] = [ // @todo Lemmy
            'type' => 'Image',
            'url' => ($this->imageUrlGet)($image),
        ];

        return $item;
    }
}
