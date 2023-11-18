<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class MediaExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly string $storageUrl
    ) {
    }

    public function getPublicPath(string $path): string
    {
        return $this->storageUrl.'/'.$path;
    }
}
