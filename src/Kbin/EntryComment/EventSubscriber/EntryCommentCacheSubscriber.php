<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\EntryComment\EventSubscriber;

use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentBeforePurgeEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentCreatedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentDeletedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentEditedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Cache\CacheInterface;

final readonly class EntryCommentCacheSubscriber
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    #[AsEventListener(event: EntryCommentDeletedEvent::class, priority: -12)]
    public function onEntryCommentDeleted(EntryCommentDeletedEvent $event): void
    {
        $this->cache->invalidateTags([
            'entry_comment_'.$event->comment->root?->getId() ?? $event->comment->getId(),
            'entry_'.$event->comment->entry->getId(),
            'user_'.$event->comment->user->getId(),
        ]);
    }

    #[AsEventListener(event: EntryCommentBeforePurgeEvent::class, priority: -12)]
    public function onPostCommentBeforePurge(EntryCommentBeforePurgeEvent $event): void
    {
        $this->cache->invalidateTags([
            'entry_'.$event->comment->entry->getId(),
            'post_comment_'.$event->comment->root?->getId() ?? $event->comment->getId(),
        ]);
    }

    #[AsEventListener(event: EntryCommentCreatedEvent::class, priority: -12)]
    public function onEntryCommentCreated(EntryCommentCreatedEvent $event): void
    {
        $this->cache->invalidateTags([
            'entry_comment_'.$event->comment->root?->getId() ?? $event->comment->getId(),
            'entry_'.$event->comment->entry->getId(),
            'user_'.$event->comment->user->getId(),
        ]);
    }

    #[AsEventListener(event: EntryCommentEditedEvent::class, priority: -12)]
    public function onEntryCommentEdited(EntryCommentEditedEvent $event): void
    {
        $this->cache->invalidateTags(['entry_comment_'.$event->comment->root?->getId() ?? $event->comment->getId()]);
    }
}
