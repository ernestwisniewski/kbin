<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Magazine\EventSubscriber;

use App\Kbin\Magazine\EventSubscriber\Event\MagazineBanEvent;
use App\Message\Notification\MagazineBanNotificationMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class MagazineNotificationSubscriber
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    #[AsEventListener(event: MagazineBanEvent::class)]
    public function onBan(MagazineBanEvent $event): void
    {
        $this->bus->dispatch(new MagazineBanNotificationMessage($event->ban->getId()));
    }
}
