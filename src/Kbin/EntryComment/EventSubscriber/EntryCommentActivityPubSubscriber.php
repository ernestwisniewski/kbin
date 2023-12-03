<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\EntryComment\EventSubscriber;

use App\Kbin\EntryComment\EventSubscriber\EntryComment\EntryCommentBeforeDeletedEvent;
use App\Kbin\EntryComment\EventSubscriber\EntryComment\EntryCommentBeforePurgeEvent;
use App\Kbin\EntryComment\EventSubscriber\EntryComment\EntryCommentCreatedEvent;
use App\Kbin\EntryComment\EventSubscriber\EntryComment\EntryCommentEditedEvent;
use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Message\ActivityPub\Outbox\UpdateMessage;
use App\Service\ActivityPub\Wrapper\DeleteWrapper;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final readonly class EntryCommentActivityPubSubscriber
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private DeleteWrapper $deleteWrapper,
    ) {
    }

    #[AsEventListener(event: EntryCommentBeforeDeletedEvent::class)]
    #[AsEventListener(event: EntryCommentBeforePurgeEvent::class)]
    public function sendApDeleteMessage(EntryCommentBeforeDeletedEvent|EntryCommentBeforePurgeEvent $event): void
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

    #[AsEventListener(event: EntryCommentCreatedEvent::class)]
    public function sendApCreateMessage(EntryCommentCreatedEvent $event): void
    {
        if (!$event->comment->apId) {
            $this->messageBus->dispatch(new CreateMessage($event->comment->getId(), \get_class($event->comment)));
        }
    }

    #[AsEventListener(event: EntryCommentEditedEvent::class)]
    public function sendApUpdateMessage(EntryCommentEditedEvent $event): void
    {
        if (!$event->comment->apId) {
            $this->messageBus->dispatch(new UpdateMessage($event->comment->getId(), \get_class($event->comment)));
        }
    }
}
