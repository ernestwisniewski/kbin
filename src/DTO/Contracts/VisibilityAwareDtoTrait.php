<?php

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
            false !== array_search($this->visibility, [
                VisibilityInterface::VISIBILITY_VISIBLE,
                VisibilityInterface::VISIBILITY_PRIVATE,
            ])
        ) {
            return $value;
        }

        array_walk($value, fn (&$val, $key) => $val = false !== array_search($key, self::$keysToDelete) ? null : $val);

        return $value;
    }
}
