<?php

declare(strict_types=1);

namespace App\Event\User;

use App\Entity\User;

class UserFollowEvent
{
    public function __construct(public User $follower, public User $following, public $unfollow = false)
    {
    }
}
