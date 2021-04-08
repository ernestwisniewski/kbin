<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\User;

class UserFollowedEvent
{
    public function __construct(public User $follower, public User $following)
    {
    }
}
