<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Entry\EventSubscriber;

use App\Kbin\Entry\EventSubscriber\Event\EntryBeforePurgeEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryCreatedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryDeletedEvent;
use App\Repository\EntryRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class EntryCounterSubscriber
{
    public function __construct(private EntryRepository $entryRepository)
    {
    }

    #[AsEventListener(event: EntryBeforePurgeEvent::class)]
    public function onEntryBeforePurge(EntryBeforePurgeEvent $event): void
    {
        $event->entry->magazine->entryCount = $this->entryRepository->countEntriesByMagazine(
            $event->entry->magazine
        ) - 1;
    }

    #[AsEventListener(event: EntryDeletedEvent::class)]
    public function onEntryDeleted(EntryDeletedEvent $event): void
    {
        $event->entry->magazine->updateEntryCounts();
    }

    #[AsEventListener(event: EntryCreatedEvent::class)]
    public function onEntryCreated(EntryCreatedEvent $event): void
    {
        $event->entry->magazine->entryCount = $this->entryRepository->countEntriesByMagazine($event->entry->magazine);
    }
}
