<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post\EventSubscriber;

use App\Kbin\Post\EventSubscriber\Event\PostBeforePurgeEvent;
use App\Kbin\Post\EventSubscriber\Event\PostCreatedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostDeletedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostEditedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostHasBeenSeenEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class PostPersistSubscriber
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[AsEventListener(event: PostCreatedEvent::class, priority: -10)]
    #[AsEventListener(event: PostEditedEvent::class, priority: -10)]
    #[AsEventListener(event: PostBeforePurgeEvent::class, priority: -10)]
    #[AsEventListener(event: PostDeletedEvent::class, priority: -10)]
    #[AsEventListener(event: PostHasBeenSeenEvent::class, priority: -10)]
    public function persist(PostCreatedEvent|PostEditedEvent|PostHasBeenSeenEvent|PostDeletedEvent|PostBeforePurgeEvent $event): void
    {
        $this->entityManager->persist($event->post);
        $this->entityManager->flush();
    }
}
