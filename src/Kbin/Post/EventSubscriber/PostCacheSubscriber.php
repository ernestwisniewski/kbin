<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Post\EventSubscriber;

use App\Kbin\Post\EventSubscriber\Event\PostCreatedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostDeletedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostEditedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Cache\CacheInterface;

final readonly class PostCacheSubscriber
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    #[AsEventListener(event: PostDeletedEvent::class)]
    public function onPostDeleted(PostDeletedEvent $event): void
    {
        $this->cache->invalidateTags([
            'post_'.$event->post->getId(),
            'user_'.$event->post->user->getId(),
        ]);
    }

    #[AsEventListener(event: PostCreatedEvent::class)]
    public function onPostCreated(PostCreatedEvent $event): void
    {
        $this->cache->invalidateTags(['user_'.$event->post->user->getId()]);
    }

    #[AsEventListener(event: PostEditedEvent::class)]
    public function onPostEdited(PostEditedEvent $event): void
    {
        $this->cache->invalidateTags(['post_'.$event->post->getId()]);
    }
}
