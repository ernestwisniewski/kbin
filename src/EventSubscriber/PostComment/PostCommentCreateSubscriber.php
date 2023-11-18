<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\PostComment;

use App\Event\PostComment\PostCommentCreatedEvent;
use App\Kbin\MessageBus\LinkEmbedMessage;
use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\Notification\PostCommentCreatedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class PostCommentCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CacheInterface $cache, private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostCommentCreatedEvent::class => 'onPostCommentCreated',
        ];
    }

    public function onPostCommentCreated(PostCommentCreatedEvent $event)
    {
        $this->cache->invalidateTags([
            'post_'.$event->comment->post->getId(),
            'post_comment_'.$event->comment->root?->getId() ?? $event->comment->getId(),
        ]);

        $this->bus->dispatch(new PostCommentCreatedNotificationMessage($event->comment->getId()));
        if ($event->comment->body) {
            $this->bus->dispatch(new LinkEmbedMessage($event->comment->body));
        }

        if (!$event->comment->apId) {
            $this->bus->dispatch(new CreateMessage($event->comment->getId(), \get_class($event->comment)));
        }
    }
}
