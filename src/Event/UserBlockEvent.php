<?php declare(strict_types = 1);

namespace App\Event;

use App\Entity\User;

class UserBlockEvent
{
    public function __construct(private User $blocker, private User $blocked)
    {
    }

    public function getBlocker(): User
    {
        return $this->blocker;
    }

    public function getBlocked(): User
    {
        return $this->blocked;
    }
}
