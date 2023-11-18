<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry\Badge;

use App\Entity\Badge;

class EntryBadgeDelete
{
    public function __construct(private EntryBadgePurge $entryBadgePurge)
    {
    }

    public function __invoke(Badge $badge): void
    {
        ($this->entryBadgePurge)($badge);
    }
}
