<?php

declare(strict_types=1);

namespace App\Kbin\Entry\Badge;

use App\Entity\Badge;
use App\Entity\Entry;
use Doctrine\Common\Collections\Collection;

class EntryBadgeAssign
{
    public function __invoke(Entry $entry, Collection $badges): Entry
    {
        $badges = $entry->magazine->badges->filter(
            static function (Badge $badge) use ($badges) {
                return $badges->contains($badge->name);
            }
        );

        $entry->setBadges(...$badges);

        return $entry;
    }
}
