<?php

declare(strict_types=1);

namespace App\Kbin\Image\Enum;

enum ImageMimeType: string
{
    case jpeg = 'image/jpeg';
    case jpg = 'image/jpg';
    case gif = 'image/gif';
    case png = 'image/png';

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getAllValuesAsString(): string
    {
        return implode(',', array_column(self::cases(), 'value'));
    }
}
