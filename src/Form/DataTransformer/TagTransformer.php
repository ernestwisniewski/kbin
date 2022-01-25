<?php declare(strict_types=1);

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class TagTransformer implements DataTransformerInterface
{
    public function __construct()
    {
    }

    public function transform($value): ?string
    {
        return $value ? implode(',', $value) : null;
    }

    public function reverseTransform($value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        return explode(',', $value);
    }
}
