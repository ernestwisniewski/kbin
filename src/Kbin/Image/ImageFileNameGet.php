<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Image;

use Symfony\Component\Mime\MimeTypesInterface;

readonly class ImageFileNameGet
{
    public function __construct(
        private MimeTypesInterface $mimeTypeGuesser,
    ) {
    }

    public function __invoke(string $file): string
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
}
