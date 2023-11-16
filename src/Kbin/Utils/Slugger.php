<?php

declare(strict_types=1);

namespace App\Kbin\Utils;

use Symfony\Component\String\Slugger\AsciiSlugger;

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
        return substr((new AsciiSlugger())->slug($this->getWords($val), '-', 'en')->toString(), 0, 255);
    }

    private function getWords(string $sentence, int $count = 10): string
    {
        preg_match("/(?:\S+(?:\W+|$)){0,$count}/", $sentence, $matches);

        return $matches[0];
    }
}
