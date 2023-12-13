<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Image;

readonly class ImageFilePathGet
{
    public function __construct(
        private ImageFileNameGet $imageFileNameGet
    ) {
    }

    public function __invoke(string $file): string
    {
        return sprintf(
            '%s/%s/%s',
            substr(($this->imageFileNameGet)($file), 0, 2),
            substr(($this->imageFileNameGet)($file), 2, 2),
            ($this->imageFileNameGet)($file)
        );
    }
}
