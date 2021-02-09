<?php declare(strict_types = 1);

namespace App\Event;

use App\Entity\User;

class UserFollowedEvent
{
    private User $follower;
    private User $following;

    public function __construct(User $follower, User $following)
    {
        $this->follower = $follower;
        $this->following = $following;
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
