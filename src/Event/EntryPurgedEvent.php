<?php declare(strict_types = 1);

namespace App\Event;

use App\Entity\Entry;

class EntryPurgedEvent
{
    protected Entry $entry;

    public function __construct(Entry $entry)
    {
        $this->entry = $entry;
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }
}
