<?php declare(strict_types=1);

namespace App\EventSubscriber\PostComment;

use App\Event\PostComment\PostCommentCreatedEvent;
use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\Notification\PostCommentCreatedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\CacheInterface;

class PostCommentCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(private CacheInterface $cache, private MessageBusInterface $bus)
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
        $this->cache->invalidateTags(['post_'.$event->comment->post->getId()]);

        $this->bus->dispatch(new PostCommentCreatedNotificationMessage($event->comment->getId()));

        if (!$event->comment->apId) {
            $this->bus->dispatch(new CreateMessage($event->comment));
        }
    }
}
