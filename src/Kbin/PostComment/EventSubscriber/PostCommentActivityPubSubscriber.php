<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\PostComment\EventSubscriber;

use App\Kbin\PostComment\EventSubscriber\Event\PostCommentBeforeDeletedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentBeforePurgeEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentCreatedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentEditedEvent;
use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Message\ActivityPub\Outbox\UpdateMessage;
use App\Service\ActivityPub\Wrapper\DeleteWrapper;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final readonly class PostCommentActivityPubSubscriber
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private DeleteWrapper $deleteWrapper,
    ) {
    }

    #[AsEventListener(event: PostCommentBeforeDeletedEvent::class)]
    #[AsEventListener(event: PostCommentBeforePurgeEvent::class)]
    public function sendApDeleteMessage(PostCommentBeforeDeletedEvent|PostCommentBeforePurgeEvent $event): void
    {
        if (!$event->comment->apId) {
            $this->messageBus->dispatch(
                new DeleteMessage(
                    $this->deleteWrapper->build($event->comment, Uuid::v4()->toRfc4122()),
                    $event->comment->user->getId(),
                    $event->comment->magazine->getId()
                )
            );
        }
    }

    #[AsEventListener(event: PostCommentCreatedEvent::class)]
    public function sendApCreateMessage(PostCommentCreatedEvent $event): void
    {
        if (!$event->comment->apId) {
            $this->messageBus->dispatch(new CreateMessage($event->comment->getId(), \get_class($event->comment)));
        }
    }

    #[AsEventListener(event: PostCommentEditedEvent::class)]
    public function sendApUpdateMessage(PostCommentEditedEvent $event): void
    {
        if (!$event->comment->apId) {
            $this->messageBus->dispatch(new UpdateMessage($event->comment->getId(), \get_class($event->comment)));
        }
    }
}
