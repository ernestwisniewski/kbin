<?php declare(strict_types=1);

namespace App\EventSubscriber\PostComment;

use App\Event\PostComment\PostCommentEditedEvent;
use App\Message\Notification\PostCommentEditedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\CacheInterface;

class PostCommentEditSubscriber implements EventSubscriberInterface
{
    public function __construct(private CacheInterface $cache, private Security $security, private MessageBusInterface $bus)
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
        $this->cache->invalidateTags(['post_'.$event->comment->post->getId()]);

        $this->bus->dispatch(new PostCommentEditedNotificationMessage($event->comment->getId()));
    }
}
