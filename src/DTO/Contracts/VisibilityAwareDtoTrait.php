<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\DTO\Contracts;

use App\Entity\Contracts\VisibilityInterface;
use OpenApi\Attributes as OA;

trait VisibilityAwareDtoTrait
{
    #[OA\Property(default: VisibilityInterface::VISIBILITY_VISIBLE, nullable: false, enum: [VisibilityInterface::VISIBILITY_PRIVATE, VisibilityInterface::VISIBILITY_TRASHED, VisibilityInterface::VISIBILITY_SOFT_DELETED, VisibilityInterface::VISIBILITY_VISIBLE])]
    public ?string $visibility = VisibilityInterface::VISIBILITY_VISIBLE;
    private static ?array $keysToDelete = null;

    private function handleDeletion(array $value): array
    {
        if (null === self::$keysToDelete) {
            throw new \LogicException('handleDeletion requires $keysToDelete to be set.');
        }
        if (
            false !== array_search($this->getVisibility(), [
                VisibilityInterface::VISIBILITY_VISIBLE,
                VisibilityInterface::VISIBILITY_PRIVATE,
            ])
        ) {
            return $value;
        }

        array_walk($value, fn (&$val, $key) => $val = false !== array_search($key, self::$keysToDelete) ? null : $val);

        return $value;
    }

    public function getVisibility(): string
    {
        return trim($this->visibility);
    }
}
