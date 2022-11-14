<?php declare(strict_types=1);

namespace App\Message\ActivityPub\Outbox;

use App\Message\Contracts\AsyncApMessageInterface;

class FollowMessage implements AsyncApMessageInterface
{
    public function __construct(public int $followerId, public int $followingId, public bool $unfollow = false)
    {
    }
}
