<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Vote\EventSubscriber;

use App\Entity\Contracts\VotableInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Kbin\Vote\EventSubscriber\Event\VoteEvent;
use App\Service\CacheService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Cache\CacheInterface;

readonly class VoteCacheSubscriber
{
    public function __construct(private CacheInterface $cache, private CacheService $cacheService)
    {
    }

    #[AsEventListener(event: VoteEvent::class, priority: -1)]
    public function onVote(VoteEvent $event): void
    {
        $this->cache->delete($this->cacheService->getVotersCacheKey($event->votable));

        match (\get_class($event->votable)) {
            EntryComment::class => $this->clearEntryCommentCache($event->votable),
            PostComment::class => $this->clearPostCommentCache($event->votable),
            Entry::class => $this->clearEntryCache($event->votable),
            Post::class => $this->clearPostCache($event->votable),
            default => null
        };
    }

    private function clearEntryCommentCache(EntryComment|VotableInterface $comment): void
    {
        $this->cache->invalidateTags(['entry_comment_'.$comment?->root?->getId() ?? $comment->getId()]);
    }

    private function clearPostCommentCache(PostComment|VotableInterface $comment): void
    {
        $this->cache->invalidateTags([
            'post_'.$comment->post->getId(),
            'post_comment_'.$comment->root?->getId() ?? $comment->getId(),
        ]);
    }

    private function clearEntryCache(Entry|VotableInterface $entry): void
    {
        $this->cache->invalidateTags([
            'entry_'.$entry->getId(),
        ]);
    }

    private function clearPostCache(Post|VotableInterface $post): void
    {
        $this->cache->invalidateTags([
            'post_'.$post->getId(),
        ]);
    }
}
