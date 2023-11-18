<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\EntryComment;

use App\Event\EntryComment\EntryCommentCreatedEvent;
use App\Kbin\MessageBus\LinkEmbedMessage;
use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\Notification\EntryCommentCreatedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class EntryCommentCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CacheInterface $cache, private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCommentCreatedEvent::class => 'onEntryCommentCreated',
        ];
    }

    public function onEntryCommentCreated(EntryCommentCreatedEvent $event): void
    {
        $this->cache->invalidateTags(['entry_comment_'.$event->comment->root?->getId() ?? $event->comment->getId()]);
        $this->cache->invalidateTags(['entry_'.$event->comment->entry->getId()]);

        $this->bus->dispatch(new EntryCommentCreatedNotificationMessage($event->comment->getId()));
        if ($event->comment->body) {
            $this->bus->dispatch(new LinkEmbedMessage($event->comment->body));
        }

        if (!$event->comment->apId) {
            $this->bus->dispatch(new CreateMessage($event->comment->getId(), \get_class($event->comment)));
        }
    }
}
