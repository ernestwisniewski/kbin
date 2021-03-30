<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Entry;
use App\Entity\User;

class EntryDeletedEvent
{
    protected Entry $entry;
    protected ?User $user = null;

    public function __construct(Entry $entry, ?User $user = null)
    {
        $this->entry = $entry;
        $this->user  = $user;
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
