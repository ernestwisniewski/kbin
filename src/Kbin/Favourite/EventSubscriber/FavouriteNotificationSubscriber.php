<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Favourite\EventSubscriber;

use App\Kbin\Favourite\EventSubscriber\Event\FavouriteEvent;
use App\Message\Notification\FavouriteNotificationMessage;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class FavouriteNotificationSubscriber
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    #[AsEventListener(event: FavouriteEvent::class)]
    public function onFavourite(FavouriteEvent $event): void
    {
        $this->messageBus->dispatch(
            new FavouriteNotificationMessage(
                $event->subject->getId(),
                ClassUtils::getRealClass(\get_class($event->subject))
            )
        );
    }
}
