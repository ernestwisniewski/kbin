<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Entry;

class EntryHasBeenSeenEvent
{
    public function __construct(public Entry $entry)
    {
    }
}
