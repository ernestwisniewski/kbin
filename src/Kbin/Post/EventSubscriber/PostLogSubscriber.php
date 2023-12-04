<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Post\EventSubscriber;

use App\Entity\MagazineLogPostDeleted;
use App\Entity\MagazineLogPostRestored;
use App\Kbin\Post\EventSubscriber\Event\PostDeletedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostRestoredEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class PostLogSubscriber
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[AsEventListener(event: PostDeletedEvent::class)]
    public function onPostDeleted(PostDeletedEvent $event): void
    {
        if (!$event->post->isTrashed()) {
            return;
        }

        if (!$event->user || $event->post->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogPostDeleted($event->post, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    #[AsEventListener(event: PostRestoredEvent::class)]
    public function onPostRestored(PostRestoredEvent $event): void
    {
        if ($event->post->isTrashed()) {
            return;
        }

        if (!$event->user || $event->post->isAuthor($event->user)) {
            return;
        }

        $log = new MagazineLogPostRestored($event->post, $event->user);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
