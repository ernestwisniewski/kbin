<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\EntryComment\EventSubscriber;

use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentCreatedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentDeletedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentPurgedEvent;
use App\Repository\EntryCommentRepository;
use App\Repository\EntryRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class EntryCommentCounterSubscriber
{
    public function __construct(private EntryRepository $entryRepository, private EntryCommentRepository $entryCommentRepository)
    {
    }

    #[AsEventListener(event: EntryCommentPurgedEvent::class)]
    public function onEntryCommentPurged(EntryCommentPurgedEvent $event): void
    {
        $event->entry->commentCount = $this->entryCommentRepository->countCommentsByEntry($event->entry);
        $event->entry->magazine->entryCommentCount = $this->entryRepository->countEntryCommentsByMagazine(
            $event->entry->magazine
        );
        $event->entry->updateRanking();
    }

    #[AsEventListener(event: EntryCommentDeletedEvent::class)]
    public function onEntryCommentDeleted(EntryCommentDeletedEvent $event): void
    {
        $magazine = $event->comment->entry->magazine;

        $event->comment->entry->commentCount = $this->entryCommentRepository->countCommentsByEntry($event->comment->entry);
        $magazine->entryCommentCount = $this->entryRepository->countEntryCommentsByMagazine($magazine) - 1;
        $event->comment->entry->updateRanking();
    }

    #[AsEventListener(event: EntryCommentCreatedEvent::class)]
    public function onEntryCommentCreated(EntryCommentCreatedEvent $event): void
    {
        $magazine = $event->comment->entry->magazine;

        $event->comment->entry->commentCount = $this->entryCommentRepository->countCommentsByEntry($event->comment->entry);
        $magazine->entryCommentCount = $this->entryRepository->countEntryCommentsByMagazine($magazine);
        $event->comment->entry->updateRanking();
    }
}
