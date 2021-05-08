<?php declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\String\Slugger\SluggerInterface;

class Slugger
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function slug(string $val): string
    {
        return $this->slugger->slug(substr($val, 0, 60))->toString();
    }

    public static function camelCase(string $value): string
    {
        return lcfirst(static::studly($value));
    }

    private static function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }
}
