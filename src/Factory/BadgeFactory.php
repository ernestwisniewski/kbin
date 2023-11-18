<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Badge;
use App\Kbin\Entry\DTO\EntryBadgeDto;

class BadgeFactory
{
    public function createDto(Badge $badge): EntryBadgeDto
    {
        return EntryBadgeDto::create(
            $badge->magazine,
            $badge->name,
            $badge->getId(),
        );
    }
}
