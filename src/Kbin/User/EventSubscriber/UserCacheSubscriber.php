<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User\EventSubscriber;

use App\Kbin\User\EventSubscriber\Event\UserFollowEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Cache\CacheInterface;

class UserCacheSubscriber
{
    public function __construct(private readonly CacheInterface $cache)
    {
    }

    #[AsEventListener(event: UserFollowEvent::class)]
    public function onUserFollow(UserFollowEvent $event): void
    {
        $this->cache->invalidateTags(['user_follow_'.$event->follower->getId()]);
    }
}
