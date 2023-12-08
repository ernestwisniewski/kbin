<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Favourite\EventSubscriber;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Kbin\Favourite\EventSubscriber\Event\FavouriteEvent;
use App\Service\CacheService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Cache\CacheInterface;

readonly class FavouriteCacheSubscriber
{
    public function __construct(private CacheInterface $cache, private CacheService $cacheService)
    {
    }

    #[AsEventListener(event: FavouriteEvent::class, priority: -12)]
    public function onFavourite(FavouriteEvent $event): void
    {
        $this->cache->delete($this->cacheService->getFavouritesCacheKey($event->subject));

        match (\get_class($event->subject)) {
            EntryComment::class => $this->clearEntryCommentCache($event->subject),
            PostComment::class => $this->clearPostCommentCache($event->subject),
            Entry::class => $this->clearEntryCache($event->subject),
            Post::class => $this->clearPostCache($event->subject),
            default => null
        };
    }

    private function clearEntryCommentCache(EntryComment|FavouriteInterface $comment): void
    {
        $this->cache->invalidateTags(['entry_comment_'.$comment->root?->getId() ?? $comment->getId()]);
    }

    private function clearPostCommentCache(PostComment|FavouriteInterface $comment): void
    {
        $this->cache->invalidateTags([
            'post_'.$comment->post->getId(),
            'post_comment_'.$comment->root?->getId() ?? $comment->getId(),
        ]);
    }

    private function clearEntryCache(Entry|FavouriteInterface $entry): void
    {
        $this->cache->invalidateTags([
            'entry_'.$entry->getId(),
        ]);
    }

    private function clearPostCache(Post|FavouriteInterface $post): void
    {
        $this->cache->invalidateTags([
            'post_'.$post->getId(),
        ]);
    }
}
