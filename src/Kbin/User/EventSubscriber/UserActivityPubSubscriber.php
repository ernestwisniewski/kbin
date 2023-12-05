<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User\EventSubscriber;

use App\Kbin\User\EventSubscriber\Event\UserFollowEvent;
use App\Message\ActivityPub\Outbox\FollowMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

class UserActivityPubSubscriber
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    #[AsEventListener(event: UserFollowEvent::class)]
    public function onUserFollow(UserFollowEvent $event): void
    {
        if (!$event->follower->apId && $event->following->apId) {
            $this->bus->dispatch(
                new FollowMessage($event->follower->getId(), $event->following->getId(), $event->unfollow)
            );
        }
    }
}
