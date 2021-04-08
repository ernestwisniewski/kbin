<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Entry;
use App\Entity\User;

class EntryDeletedEvent
{
    public function __construct(public Entry $entry, public ?User $user = null)
    {
    }
}
