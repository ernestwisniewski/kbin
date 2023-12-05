<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EntryComment\EventSubscriber;

use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentBeforePurgeEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentCreatedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentDeletedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentEditedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentPurgedEvent;
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
    #[AsEventListener(event: EntryCommentDeletedEvent::class, priority: -10)]
    public function persist(
        EntryCommentCreatedEvent|EntryCommentEditedEvent|EntryCommentBeforePurgeEvent|EntryCommentDeletedEvent $event
    ): void {
        $this->entityManager->persist($event->comment);
        $this->entityManager->flush();
    }

    #[AsEventListener(event: EntryCommentPurgedEvent::class, priority: -10)]
    public function persistOnPurge(EntryCommentPurgedEvent $event): void
    {
        $this->entityManager->flush();
    }
}
