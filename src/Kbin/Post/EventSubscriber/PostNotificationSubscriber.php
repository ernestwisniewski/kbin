<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Post\EventSubscriber;

use App\Kbin\Post\EventSubscriber\Event\PostBeforePurgeEvent;
use App\Kbin\Post\EventSubscriber\Event\PostCreatedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostDeletedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostEditedEvent;
use App\Message\Notification\PostCreatedNotificationMessage;
use App\Message\Notification\PostDeletedNotificationMessage;
use App\Message\Notification\PostEditedNotificationMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class PostNotificationSubscriber
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    #[AsEventListener(event: PostDeletedEvent::class)]
    #[AsEventListener(event: PostBeforePurgeEvent::class)]
    public function sendDeletedNotification(PostDeletedEvent|PostBeforePurgeEvent $event): void
    {
        $this->messageBus->dispatch(new PostDeletedNotificationMessage($event->post->getId()));
    }

    #[AsEventListener(event: PostCreatedEvent::class)]
    public function sendCreatedNotification(PostCreatedEvent $event): void
    {
        $this->messageBus->dispatch(new PostCreatedNotificationMessage($event->post->getId()));
    }

    #[AsEventListener(event: PostEditedEvent::class)]
    public function sendEditedNotification(PostEditedEvent $event): void
    {
        $this->messageBus->dispatch(new PostEditedNotificationMessage($event->post->getId()));
    }
}
