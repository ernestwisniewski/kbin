<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\DataTransformerInterface;

class BadgeCollectionToStringTransformer implements DataTransformerInterface
{
    public function transform($value): string
    {
        if ($value instanceof Collection) {
            $value = $value->toArray();
            natcasesort($value);
        } elseif (null !== $value) {
            throw new \TypeError(sprintf('$value must be array or NULL, %s given', get_debug_type($value)));
        }

        return implode(', ', $value ?? []);
    }

    public function reverseTransform($value): ArrayCollection
    {
        if (\is_string($value)) {
            return new ArrayCollection(preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY));
        }

        if (null !== $value) {
            throw new \TypeError(sprintf('$value must be string or NULL, %s given', get_debug_type($value)));
        }

        return new ArrayCollection();
    }
}
