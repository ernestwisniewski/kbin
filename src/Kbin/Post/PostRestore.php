<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Post;
use App\Entity\User;
use App\Kbin\Contract\RestoreContentServiceInterface;
use App\Kbin\Post\EventSubscriber\Event\PostRestoredEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class PostRestore implements RestoreContentServiceInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user, ContentInterface|Post $subject): void
    {
        if (VisibilityInterface::VISIBILITY_TRASHED !== $subject->getVisibility()) {
            throw new \Exception('Invalid visibility');
        }

        $subject->visibility = VisibilityInterface::VISIBILITY_VISIBLE;

        $this->entityManager->persist($subject);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostRestoredEvent($subject, $user));
    }
}
