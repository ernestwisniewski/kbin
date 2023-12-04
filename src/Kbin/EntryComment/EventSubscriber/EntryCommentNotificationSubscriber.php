<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\EntryComment\EventSubscriber;

use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentBeforePurgeEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentCreatedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentDeletedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentEditedEvent;
use App\Message\Notification\EntryCommentCreatedNotificationMessage;
use App\Message\Notification\EntryCommentDeletedNotificationMessage;
use App\Message\Notification\EntryCommentEditedNotificationMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class EntryCommentNotificationSubscriber
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    #[AsEventListener(event: EntryCommentDeletedEvent::class)]
    #[AsEventListener(event: EntryCommentBeforePurgeEvent::class)]
    public function sendDeletedNotification(EntryCommentDeletedEvent|EntryCommentBeforePurgeEvent $event): void
    {
        $this->messageBus->dispatch(new EntryCommentDeletedNotificationMessage($event->comment->getId()));
    }

    #[AsEventListener(event: EntryCommentCreatedEvent::class)]
    public function sendCreatedNotification(EntryCommentCreatedEvent $event): void
    {
        $this->messageBus->dispatch(new EntryCommentCreatedNotificationMessage($event->comment->getId()));
    }

    #[AsEventListener(event: EntryCommentEditedEvent::class)]
    public function sendEditedNotification(EntryCommentEditedEvent $event): void
    {
        $this->messageBus->dispatch(new EntryCommentEditedNotificationMessage($event->comment->getId()));
    }
}
