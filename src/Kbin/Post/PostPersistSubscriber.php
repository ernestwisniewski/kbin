<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post;

use App\EventSubscriber\Post\PostShowSubscriber;
use App\Kbin\Post\EventSubscriber\Event\PostCreatedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostEditedEvent;
use App\Kbin\Post\EventSubscriber\PostCounterSubscriber;
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
    #[AsEventListener(event: PostShowSubscriber::class, priority: -10)]
    #[AsEventListener(event: PostCounterSubscriber::class, priority: -10)]
    public function persist(PostCreatedEvent|PostEditedEvent|PostShowSubscriber|PostCounterSubscriber $event): void
    {
        $this->entityManager->persist($event->post);
        $this->entityManager->flush();
    }
}
