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

readonly class PostLangChange
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Post $post, string $lang = 'en'): void
    {
        $post->lang = $lang;

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostEditedEvent($post));
    }
}
