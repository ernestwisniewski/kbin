<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Image;

use League\Flysystem\FilesystemOperator;

readonly class ImageRemove
{
    public function __construct(
        private FilesystemOperator $publicUploadsFilesystem,
    ) {
    }

    public function __invoke(string $path): void
    {
        $this->publicUploadsFilesystem->delete($path);
    }
}
