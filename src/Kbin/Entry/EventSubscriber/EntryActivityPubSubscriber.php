<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Entry\EventSubscriber;

use App\Kbin\Entry\EventSubscriber\Event\EntryBeforeDeletedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryBeforePurgeEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryCreatedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryEditedEvent;
use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Message\ActivityPub\Outbox\UpdateMessage;
use App\Service\ActivityPub\Wrapper\DeleteWrapper;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final readonly class EntryActivityPubSubscriber
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private DeleteWrapper $deleteWrapper,
    ) {
    }

    #[AsEventListener(event: EntryBeforeDeletedEvent::class)]
    #[AsEventListener(event: EntryBeforePurgeEvent::class)]
    public function sendApDeleteMessage(EntryBeforeDeletedEvent|EntryBeforePurgeEvent $event): void
    {
        if (!$event->entry->apId) {
            $this->messageBus->dispatch(
                new DeleteMessage(
                    $this->deleteWrapper->build($event->entry, Uuid::v4()->toRfc4122()),
                    $event->entry->user->getId(),
                    $event->entry->magazine->getId()
                )
            );
        }
    }

    #[AsEventListener(event: EntryCreatedEvent::class)]
    public function sendApCreateMessage(EntryCreatedEvent $event): void
    {
        if (!$event->entry->apId) {
            $this->messageBus->dispatch(new CreateMessage($event->entry->getId(), \get_class($event->entry)));
        }
    }

    #[AsEventListener(event: EntryEditedEvent::class)]
    public function sendApUpdateMessage(EntryEditedEvent $event): void
    {
        if (!$event->entry->apId) {
            $this->messageBus->dispatch(new UpdateMessage($event->entry->getId(), \get_class($event->entry)));
        }
    }
}