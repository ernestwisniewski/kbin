<?php

declare(strict_types=1);

namespace App\EventSubscriber\PostComment;

use App\Event\PostComment\PostCommentEditedEvent;
use App\Message\ActivityPub\Outbox\UpdateMessage;
use App\Message\Notification\PostCommentEditedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class PostCommentEditSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly MessageBusInterface $bus
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostCommentEditedEvent::class => 'onPostCommentEdited',
        ];
    }

    public function onPostCommentEdited(PostCommentEditedEvent $event)
    {
        $this->cache->invalidateTags([
            'post_'.$event->comment->post->getId(),
            'post_comment_'.$event->comment->root?->getId() ?? $event->comment->getId()
        ]);

        $this->bus->dispatch(new PostCommentEditedNotificationMessage($event->comment->getId()));

        if (!$event->comment->apId) {
            $this->bus->dispatch(new UpdateMessage($event->comment->getId(), get_class($event->comment)));
        }
    }
}
