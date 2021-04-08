<?php declare(strict_types=1);

namespace App\Utils;

class Slugger
{
    public function camelCase(string $value): string
    {
        return lcfirst($this->studly($value));
    }

    private function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }
}
