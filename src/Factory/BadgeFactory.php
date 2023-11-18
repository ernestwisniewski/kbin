<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

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
