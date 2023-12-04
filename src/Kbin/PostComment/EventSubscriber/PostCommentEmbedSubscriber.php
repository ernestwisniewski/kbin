<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\PostComment\EventSubscriber;

use App\Kbin\MessageBus\LinkEmbedMessage;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentCreatedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentEditedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class PostCommentEmbedSubscriber
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    #[AsEventListener(event: PostCommentCreatedEvent::class)]
    #[AsEventListener(event: PostCommentEditedEvent::class)]
    public function attachEmbed(PostCommentCreatedEvent|PostCommentEditedEvent $event): void
    {
        if ($event->comment->body) {
            $this->messageBus->dispatch(new LinkEmbedMessage($event->comment->body));
        }
    }
}
