<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Post;
use App\Entity\User;
use App\Event\Post\PostBeforeDeletedEvent;
use App\Event\Post\PostDeletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class PostTrash
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user, Post $post): void
    {
        $post->trash();

        $this->eventDispatcher->dispatch(new PostBeforeDeletedEvent($post, $user));

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostDeletedEvent($post, $user));
    }
}
