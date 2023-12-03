<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Entry\EventSubscriber;

use App\Kbin\Entry\EventSubscriber\Event\EntryBeforePurgeEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryCreatedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryDeletedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryEditedEvent;
use App\Message\Notification\EntryCreatedNotificationMessage;
use App\Message\Notification\EntryDeletedNotificationMessage;
use App\Message\Notification\EntryEditedNotificationMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class EntryNotificationSubscriber
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    #[AsEventListener(event: EntryDeletedEvent::class)]
    #[AsEventListener(event: EntryBeforePurgeEvent::class)]
    public function sendDeletedNotification(EntryDeletedEvent|EntryBeforePurgeEvent $event): void
    {
        $this->messageBus->dispatch(new EntryDeletedNotificationMessage($event->entry->getId()));
    }

    #[
        AsEventListener(event: EntryCreatedEvent::class)]
    public function sendCreatedNotification(EntryCreatedEvent $event): void
    {
        $this->messageBus->dispatch(new EntryCreatedNotificationMessage($event->entry->getId()));
    }

    #[AsEventListener(event: EntryEditedEvent::class)]
    public function sendEditedNotification(EntryEditedEvent $event): void
    {
        $this->messageBus->dispatch(new EntryEditedNotificationMessage($event->entry->getId()));
    }
}
