<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry\EventSubscriber;

use App\Kbin\Entry\EventSubscriber\Event\EntryBeforePurgeEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryCreatedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryDeletedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryEditedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryHasBeenSeenEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class EntryPersistSubscriber
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[AsEventListener(event: EntryCreatedEvent::class, priority: -10)]
    #[AsEventListener(event: EntryEditedEvent::class, priority: -10)]
    #[AsEventListener(event: EntryBeforePurgeEvent::class, priority: -10)]
    #[AsEventListener(event: EntryDeletedEvent::class, priority: -10)]
    #[AsEventListener(event: EntryHasBeenSeenEvent::class, priority: -10)]
    public function persist(EntryCreatedEvent|EntryEditedEvent|EntryBeforePurgeEvent|EntryDeletedEvent|EntryHasBeenSeenEvent $event): void
    {
        $this->entityManager->persist($event->entry);
        $this->entityManager->flush();
    }
}
