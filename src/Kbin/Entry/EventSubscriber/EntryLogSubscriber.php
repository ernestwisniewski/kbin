<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Entry\EventSubscriber;

use App\Entity\MagazineLogEntryDeleted;
use App\Entity\MagazineLogEntryRestored;
use App\Kbin\Entry\EventSubscriber\Event\EntryDeletedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryRestoredEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class EntryLogSubscriber
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[AsEventListener(event: EntryDeletedEvent::class)]
    public function onEntryDeleted(EntryDeletedEvent $event): void
    {
        if (!$event->entry->isTrashed()) {
            return;
        }

        if (!$event->user || $event->entry->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogEntryDeleted($event->entry, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    #[AsEventListener(event: EntryRestoredEvent::class)]
    public function onEntryRestored(EntryRestoredEvent $event): void
    {
        if ($event->entry->isTrashed()) {
            return;
        }

        if (!$event->user || $event->entry->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogEntryRestored($event->entry, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
