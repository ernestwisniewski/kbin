<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\PostComment\EventSubscriber;

use App\Kbin\PostComment\EventSubscriber\Event\PostCommentBeforePurgeEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentCreatedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentDeletedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentEditedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Cache\CacheInterface;

final readonly class PostCommentCacheSubscriber
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    #[AsEventListener(event: PostCommentDeletedEvent::class)]
    public function onPostCommentDeleted(PostCommentDeletedEvent $event): void
    {
        $this->cache->invalidateTags([
            'post_'.$event->comment->post->getId(),
            'post_comment_'.$event->comment->root?->getId() ?? $event->comment->getId(),
            'user_'.$event->comment->user->getId(),
        ]);
    }

    #[AsEventListener(event: PostCommentBeforePurgeEvent::class, priority: -12)]
    public function onPostCommentBeforePurge(PostCommentBeforePurgeEvent $event): void
    {
        $this->cache->invalidateTags([
            'post_'.$event->comment->post->getId(),
            'post_comment_'.$event->comment->root?->getId() ?? $event->comment->getId(),
        ]);
    }

    #[AsEventListener(event: PostCommentCreatedEvent::class, priority: -12)]
    public function onPostCommentCreated(PostCommentCreatedEvent $event): void
    {
        $this->cache->invalidateTags([
            'post_'.$event->comment->post->getId(),
            'post_comment_'.$event->comment->root?->getId() ?? $event->comment->getId(),
            'user_'.$event->comment->user->getId(),
        ]);
    }

    #[AsEventListener(event: PostCommentEditedEvent::class, priority: -12)]
    public function onPostCommentEdited(PostCommentEditedEvent $event): void
    {
        $this->cache->invalidateTags([
            'post_'.$event->comment->post->getId(),
            'post_comment_'.$event->comment->root?->getId() ?? $event->comment->getId(),
        ]);
    }
}
