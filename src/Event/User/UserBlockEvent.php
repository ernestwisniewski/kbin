<?php declare(strict_types = 1);

namespace App\Event\User;

use App\Entity\User;

class UserBlockEvent
{
    public function __construct(public User $blocker, public User $blocked)
    {
    }
}
