<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\EntryComment\EventSubscriber;

use App\Entity\MagazineLogEntryCommentDeleted;
use App\Entity\MagazineLogEntryCommentRestored;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentDeletedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentRestoredEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class EntryCommentLogSubscriber
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[AsEventListener(event: EntryCommentDeletedEvent::class)]
    public function onEntryCommentDeleted(EntryCommentDeletedEvent $event): void
    {
        if (!$event->comment->isTrashed()) {
            return;
        }

        if (!$event->user || $event->comment->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogEntryCommentDeleted($event->comment, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    #[AsEventListener(event: EntryCommentRestoredEvent::class)]
    public function onEntryCommentRestored(EntryCommentRestoredEvent $event): void
    {
        if ($event->comment->isTrashed()) {
            return;
        }

        if (!$event->user || $event->comment->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogEntryCommentRestored($event->comment, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
