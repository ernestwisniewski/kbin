<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\PostComment\EventSubscriber;

use App\Kbin\PostComment\EventSubscriber\Event\PostCommentBeforePurgeEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentCreatedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentDeletedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentEditedEvent;
use App\Message\Notification\PostCommentCreatedNotificationMessage;
use App\Message\Notification\PostCommentDeletedNotificationMessage;
use App\Message\Notification\PostCommentEditedNotificationMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class PostCommentNotificationSubscriber
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    #[AsEventListener(event: PostCommentDeletedEvent::class)]
    #[AsEventListener(event: PostCommentBeforePurgeEvent::class)]
    public function sendDeletedNotification(PostCommentDeletedEvent|PostCommentBeforePurgeEvent $event): void
    {
        $this->messageBus->dispatch(new PostCommentDeletedNotificationMessage($event->comment->getId()));
    }

    #[AsEventListener(event: PostCommentCreatedEvent::class)]
    public function sendCreatedNotification(PostCommentCreatedEvent $event): void
    {
        $this->messageBus->dispatch(new PostCommentCreatedNotificationMessage($event->comment->getId()));
    }

    #[AsEventListener(event: PostCommentEditedEvent::class)]
    public function sendEditedNotification(PostCommentEditedEvent $event): void
    {
        $this->messageBus->dispatch(new PostCommentEditedNotificationMessage($event->comment->getId()));
    }
}
