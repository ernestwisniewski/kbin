<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\Entry;

use App\Event\Entry\EntryBeforeDeletedEvent;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Service\ActivityPub\Wrapper\DeleteWrapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class EntryBeforeDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly DeleteWrapper $deleteWrapper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryBeforeDeletedEvent::class => 'onEntryBeforeDeleted',
        ];
    }

    public function onEntryBeforeDeleted(EntryBeforeDeletedEvent $event): void
    {
        if (!$event->entry->apId) {
            $this->bus->dispatch(
                new DeleteMessage(
                    $this->deleteWrapper->build($event->entry, Uuid::v4()->toRfc4122()),
                    $event->entry->user->getId(),
                    $event->entry->magazine->getId()
                )
            );
        }
    }
}
