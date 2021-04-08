<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Entry;

class EntryUpdatedEvent
{
    public function __construct(public Entry $entry)
    {
    }
}
