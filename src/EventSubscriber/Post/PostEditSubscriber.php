<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\Post;

use App\Event\Post\PostEditedEvent;
use App\Kbin\MessageBus\LinkEmbedMessage;
use App\Message\ActivityPub\Outbox\UpdateMessage;
use App\Message\Notification\PostEditedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class PostEditSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus, private readonly CacheInterface $cache)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostEditedEvent::class => 'onPostEdited',
        ];
    }

    public function onPostEdited(PostEditedEvent $event)
    {
        $this->bus->dispatch(new PostEditedNotificationMessage($event->post->getId()));
        if ($event->post->body) {
            $this->bus->dispatch(new LinkEmbedMessage($event->post->body));
        }

        if (!$event->post->apId) {
            $this->bus->dispatch(new UpdateMessage($event->post->getId(), \get_class($event->post)));
        }

        $this->cache->invalidateTags([
            'post_'.$event->post->getId(),
        ]);
    }
}
