<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Post\EventSubscriber;

use App\Kbin\MessageBus\LinkEmbedMessage;
use App\Kbin\Post\EventSubscriber\Event\PostCreatedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostEditedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class PostEmbedSubscriber
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    #[AsEventListener(event: PostCreatedEvent::class)]
    #[AsEventListener(event: PostEditedEvent::class)]
    public function attachEmbed(PostCreatedEvent|PostEditedEvent $event): void
    {
        if ($event->post->body) {
            $this->messageBus->dispatch(new LinkEmbedMessage($event->post->body));
        }
    }
}
