<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine\EventSubscriber;

use App\Kbin\Magazine\EventSubscriber\Event\MagazineSubscribedEvent;
use App\Message\ActivityPub\Outbox\FollowMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class MagazineActivityPubSubscriber
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    #[AsEventListener(event: MagazineSubscribedEvent::class)]
    public function onMagazineFollow(MagazineSubscribedEvent $event): void
    {
        if ($event->magazine->apId && !$event->user->apId) {
            $this->bus->dispatch(
                new FollowMessage($event->user->getId(), $event->magazine->getId(), $event->unfollow, true)
            );
        }
    }
}
