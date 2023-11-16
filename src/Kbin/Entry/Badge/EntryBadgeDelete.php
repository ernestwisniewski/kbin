<?php

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
