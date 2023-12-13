<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Image;

use App\Kbin\Image\Enum\ImageMimeType;

class ImageUrlCheck
{
    public function __invoke(string $url): bool
    {
        $urlExt = pathinfo($url, PATHINFO_EXTENSION);

        $types = array_map(fn ($type) => str_replace('image/', '', $type), ImageMimeType::getAllValues());

        return \in_array($urlExt, $types);
    }
}
