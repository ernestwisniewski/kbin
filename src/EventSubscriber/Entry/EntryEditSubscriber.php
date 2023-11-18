<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\Entry;

use App\Event\Entry\EntryEditedEvent;
use App\Message\ActivityPub\Outbox\UpdateMessage;
use App\Message\LinkEmbedMessage;
use App\Message\Notification\EntryEditedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class EntryEditSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus, private readonly CacheInterface $cache)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryEditedEvent::class => 'onEntryEdited',
        ];
    }

    public function onEntryEdited(EntryEditedEvent $event): void
    {
        $this->bus->dispatch(new EntryEditedNotificationMessage($event->entry->getId()));
        if ($event->entry->body) {
            $this->bus->dispatch(new LinkEmbedMessage($event->entry->body));
        }

        if (!$event->entry->apId) {
            $this->bus->dispatch(new UpdateMessage($event->entry->getId(), \get_class($event->entry)));
        }

        $this->cache->invalidateTags([
            'entry_'.$event->entry->getId(),
        ]);
    }
}
