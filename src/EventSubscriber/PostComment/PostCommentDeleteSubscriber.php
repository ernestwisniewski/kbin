<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\PostComment;

use App\Event\PostComment\PostCommentBeforePurgeEvent;
use App\Event\PostComment\PostCommentDeletedEvent;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Message\Notification\PostCommentDeletedNotificationMessage;
use App\Service\ActivityPub\Wrapper\DeleteWrapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\CacheInterface;

class PostCommentDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly MessageBusInterface $bus,
        private readonly DeleteWrapper $deleteWrapper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostCommentDeletedEvent::class => 'onPostCommentDeleted',
            PostCommentBeforePurgeEvent::class => 'onPostCommentBeforePurge',
        ];
    }

    public function onPostCommentDeleted(PostCommentDeletedEvent $event): void
    {
        $this->cache->invalidateTags([
            'post_'.$event->comment->post->getId(),
            'post_comment_'.$event->comment->root?->getId() ?? $event->comment->getId(),
            'user_'.$event->comment->user->getId(),
        ]);

        $this->bus->dispatch(new PostCommentDeletedNotificationMessage($event->comment->getId()));
    }

    public function onPostCommentBeforePurge(PostCommentBeforePurgeEvent $event): void
    {
        $this->cache->invalidateTags([
            'post_'.$event->comment->post->getId(),
            'post_comment_'.$event->comment->root?->getId() ?? $event->comment->getId(),
        ]);

        $this->bus->dispatch(new PostCommentDeletedNotificationMessage($event->comment->getId()));

        if (!$event->comment->apId) {
            $this->bus->dispatch(
                new DeleteMessage(
                    $this->deleteWrapper->build($event->comment, Uuid::v4()->toRfc4122()),
                    $event->comment->user->getId(),
                    $event->comment->magazine->getId()
                )
            );
        }
    }
}
