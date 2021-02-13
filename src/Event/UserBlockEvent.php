<?php declare(strict_types = 1);

namespace App\Event;

use App\Entity\User;

class UserBlockEvent
{
    private User $blocker;
    private User $blocked;

    public function __construct(User $blocker, User $blocked)
    {
        $this->blocker = $blocker;
        $this->blocked = $blocked;
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
