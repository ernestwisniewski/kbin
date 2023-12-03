<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EntryComment\EventSubscriber;

use App\Kbin\EntryComment\EventSubscriber\EntryComment\EntryCommentBeforePurgeEvent;
use App\Kbin\EntryComment\EventSubscriber\EntryComment\EntryCommentCreatedEvent;
use App\Kbin\EntryComment\EventSubscriber\EntryComment\EntryCommentDeletedEvent;
use App\Kbin\EntryComment\EventSubscriber\EntryComment\EntryCommentEditedEvent;
use App\Kbin\EntryComment\EventSubscriber\EntryComment\EntryCommentPurgedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class EntryCommentPersistSubscriber
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[AsEventListener(event: EntryCommentCreatedEvent::class, priority: -10)]
    #[AsEventListener(event: EntryCommentEditedEvent::class, priority: -10)]
    #[AsEventListener(event: EntryCommentBeforePurgeEvent::class, priority: -10)]
    #[AsEventListener(event: EntryCommentPurgedEvent::class, priority: -10)]
    #[AsEventListener(event: EntryCommentDeletedEvent::class, priority: -10)]
    public function persist(
        EntryCommentCreatedEvent|EntryCommentEditedEvent|EntryCommentBeforePurgeEvent|EntryCommentPurgedEvent|EntryCommentDeletedEvent $event
    ): void {
        $this->entityManager->persist($event->comment);
        $this->entityManager->flush();
    }
}
