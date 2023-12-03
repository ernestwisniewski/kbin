<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry\EventSubscriber;

use App\Entity\Notification;
use App\Kbin\Entry\EventSubscriber\Event\EntryCreatedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryEditedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryHasBeenSeenEvent;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class EntryPersistSubscriber
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[AsEventListener(event: EntryCreatedEvent::class, priority: -10)]
    #[AsEventListener(event: EntryEditedEvent::class, priority: -10)]
    #[AsEventListener(event: EntryShowSubscriber::class, priority: -10)]
    #[AsEventListener(event: EntryCounterSubscriber::class, priority: -10)]
    public function persist(EntryCreatedEvent|EntryEditedEvent|EntryShowSubscriber|EntryCounterSubscriber $event): void
    {
        $this->entityManager->persist($event->entry);
        $this->entityManager->flush();
    }
}
