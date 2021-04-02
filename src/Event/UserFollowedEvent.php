<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\User;

class UserFollowedEvent
{
    public function __construct(private User $follower, private User $following)
    {
    }

    public function getFollower(): User
    {
        return $this->follower;
    }

    public function getFollowing(): User
    {
        return $this->following;
    }
}
