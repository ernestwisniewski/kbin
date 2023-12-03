<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Entry\EventSubscriber;

use App\Kbin\Entry\EventSubscriber\Event\EntryBeforeDeletedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryCreatedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryDeletedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryEditedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Cache\CacheInterface;

final readonly class EntryCacheSubscriber
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    #[AsEventListener(event: EntryDeletedEvent::class)]
    public function onEntryDeleted(EntryDeletedEvent $event): void
    {
        $this->cache->invalidateTags([
            'entry_'.$event->entry->getId(),
            'user_'.$event->entry->user->getId(),
        ]);
    }

    #[AsEventListener(event: EntryCreatedEvent::class)]
    public function onEntryCreated(EntryCreatedEvent $event): void
    {
        $this->cache->invalidateTags(['user_'.$event->entry->user->getId()]);
    }

    #[AsEventListener(event: EntryEditedEvent::class)]
    public function onEntryEdited(EntryEditedEvent $event): void
    {
        $this->cache->invalidateTags(['entry_'.$event->entry->getId()]);
    }
}