<?php declare(strict_types=1);

namespace App\EventSubscriber\PostComment;

use App\Event\PostComment\PostCommentEditedEvent;
use App\Message\Notification\PostCommentEditedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;

class PostCommentEditSubscriber implements EventSubscriberInterface
{
    public function __construct(private CacheInterface $cache, private MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostCommentEditedEvent::class => 'onPostCommentEdited',
        ];
    }

    public function onPostCommentEdited(PostCommentEditedEvent $event)
    {
        $this->cache->delete('post_comments_preview_'.$event->comment->getId());
        $this->bus->dispatch(new PostCommentEditedNotificationMessage($event->comment->getId()));
    }
}
