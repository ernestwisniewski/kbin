<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\EntryComment\EventSubscriber;

use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentCreatedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentDeletedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentPurgedEvent;
use App\Repository\EntryRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class EntryCommentCounterSubscriber
{
    public function __construct(private EntryRepository $entryRepository)
    {
    }

    #[AsEventListener(event: EntryCommentPurgedEvent::class)]
    public function onEntryCommentPurged(EntryCommentPurgedEvent $event): void
    {
        $event->magazine->entryCommentCount = $this->entryRepository->countEntryCommentsByMagazine($event->magazine);
    }

    #[AsEventListener(event: EntryCommentDeletedEvent::class)]
    public function onEntryCommentDeleted(EntryCommentDeletedEvent $event): void
    {
        $magazine = $event->comment->entry->magazine;
        $magazine->entryCommentCount = $this->entryRepository->countEntryCommentsByMagazine($magazine) - 1;

        $event->comment->entry->updateCounts();
    }

    #[AsEventListener(event: EntryCommentCreatedEvent::class)]
    public function onEntryCommentCreated(EntryCommentCreatedEvent $event): void
    {
        $magazine = $event->comment->entry->magazine;
        $magazine->entryCommentCount = $this->entryRepository->countEntryCommentsByMagazine($magazine);
    }
}
