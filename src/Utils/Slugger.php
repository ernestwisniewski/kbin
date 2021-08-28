<?php declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\String\Slugger\AsciiSlugger;
use \ForceUTF8\Encoding;

class Slugger
{
    public static function camelCase(string $value): string
    {
        return lcfirst(static::studly($value));
    }

    private static function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }

    public function slug(string $val): string
    {
        return (new AsciiSlugger())->slug(substr(Encoding::fixUTF8($val), 0, 60))->toString();
    }
}
