<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Favourite\EventSubscriber;

use App\Kbin\Favourite\EventSubscriber\Event\FavouriteEvent;
use App\Message\ActivityPub\Outbox\LikeMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

class FavouriteActivityPubSubscriber
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    #[AsEventListener(event: FavouriteEvent::class)]
    public function onFavourite(FavouriteEvent $event): void
    {
        if (!$event->user->apId) {
            $this->messageBus->dispatch(
                new LikeMessage(
                    $event->user->getId(),
                    $event->subject->getId(),
                    \get_class($event->subject),
                    $event->removeLike
                ),
            );
        }
    }
}
