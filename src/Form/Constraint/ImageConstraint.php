<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Form\Constraint;

use App\Kbin\Image\Enum\ImageMimeType;
use Symfony\Component\Validator\Constraints\Image;

class ImageConstraint
{
    public static function default(): Image
    {
        return new Image(
            [
                'detectCorrupted' => true,
                'groups' => ['upload'],
                'maxSize' => '12M',
                'mimeTypes' => ImageMimeType::getAllValues(),
            ]
        );
    }
}
