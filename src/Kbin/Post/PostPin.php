<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Post;
use App\Kbin\Post\EventSubscriber\Event\PostEditedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class PostPin
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Post $post): Post
    {
        $post->sticky = !$post->sticky;

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostEditedEvent($post));

        return $post;
    }
}
