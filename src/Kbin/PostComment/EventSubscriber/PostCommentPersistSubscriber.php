<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\PostComment\EventSubscriber;

use App\Kbin\PostComment\EventSubscriber\Event\PostCommentBeforePurgeEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentCreatedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentDeletedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentEditedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentPurgedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class PostCommentPersistSubscriber
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[AsEventListener(event: PostCommentCreatedEvent::class, priority: -10)]
    #[AsEventListener(event: PostCommentEditedEvent::class, priority: -10)]
    #[AsEventListener(event: PostCommentBeforePurgeEvent::class, priority: -10)]
    #[AsEventListener(event: PostCommentDeletedEvent::class, priority: -10)]
    public function persist(
        PostCommentCreatedEvent|PostCommentEditedEvent|PostCommentBeforePurgeEvent|PostCommentDeletedEvent $event
    ): void {
        $this->entityManager->persist($event->comment);
        $this->entityManager->flush();
    }

    #[AsEventListener(event: PostCommentPurgedEvent::class, priority: -10)]
    public function persistOnPurge(PostCommentPurgedEvent $event): void
    {
        $this->entityManager->flush();
    }
}
