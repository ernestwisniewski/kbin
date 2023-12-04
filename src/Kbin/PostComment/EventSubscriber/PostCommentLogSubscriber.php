<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\PostComment\EventSubscriber;

use App\Entity\MagazineLogPostCommentDeleted;
use App\Entity\MagazineLogPostCommentRestored;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentDeletedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentRestoredEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class PostCommentLogSubscriber
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[AsEventListener(event: PostCommentDeletedEvent::class)]
    public function onPostCommentDeleted(PostCommentDeletedEvent $event): void
    {
        if (!$event->comment->isTrashed()) {
            return;
        }

        if (!$event->user || $event->comment->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogPostCommentDeleted($event->comment, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    #[AsEventListener(event: PostCommentRestoredEvent::class)]
    public function onPostCommentRestored(PostCommentRestoredEvent $event): void
    {
        if ($event->comment->isTrashed()) {
            return;
        }

        if (!$event->user || $event->comment->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogPostCommentRestored($event->comment, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
