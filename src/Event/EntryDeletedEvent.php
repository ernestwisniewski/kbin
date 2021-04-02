<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Entry;
use App\Entity\User;

class EntryDeletedEvent
{
    public function __construct(private Entry $entry, private ?User $user = null)
    {
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
