<?php declare(strict_types=1);

namespace App\EventSubscriber\PostComment;

use App\Event\PostComment\PostCommentBeforePurgeEvent;
use App\Event\PostComment\PostCommentDeletedEvent;
use App\Message\Notification\PostCommentDeletedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class PostCommentDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(private CacheInterface $cache, private MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostCommentDeletedEvent::class     => 'onPostCommentDeleted',
            PostCommentBeforePurgeEvent::class => 'onPostCommentBeforePurge',
        ];
    }

    public function onPostCommentDeleted(PostCommentDeletedEvent $event): void
    {
        $this->cache->delete('comments_preview_post_'.$event->comment->post->getId());
        $this->bus->dispatch(new PostCommentDeletedNotificationMessage($event->comment->getId()));
    }

    public function onPostCommentBeforePurge(PostCommentBeforePurgeEvent $event): void
    {
        $this->cache->delete('comments_preview_post_'.$event->comment->post->getId());
        $this->bus->dispatch(new PostCommentDeletedNotificationMessage($event->comment->getId()));
    }
}
