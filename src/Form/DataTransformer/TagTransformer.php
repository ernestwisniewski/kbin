<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class TagTransformer implements DataTransformerInterface
{
    public function transform($value): ?string
    {
        return $value ? implode(',', $value) : null;
    }

    public function reverseTransform($value): ?array
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return explode(',', $value);
    }
}
