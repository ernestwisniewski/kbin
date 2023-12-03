<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Entry\EventSubscriber;

use App\Kbin\Entry\EventSubscriber\Event\EntryCreatedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryEditedEvent;
use App\Kbin\Entry\MessageBus\EntryEmbedAttachMessage;
use App\Kbin\MessageBus\LinkEmbedMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class EntryEmbedSubscriber
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    #[AsEventListener(event: EntryCreatedEvent::class)]
    #[AsEventListener(event: EntryEditedEvent::class)]
    public function attachEmbed(EntryCreatedEvent|EntryEditedEvent $event): void
    {
        $this->messageBus->dispatch(new EntryEmbedAttachMessage($event->entry->getId()));

        if ($event->entry->body) {
            $this->messageBus->dispatch(new LinkEmbedMessage($event->entry->body));
        }
    }
}
