<?php declare(strict_types=1);

namespace App\EventSubscriber\PostComment;

use App\Event\PostComment\PostCommentEditedEvent;
use App\Message\Notification\PostCommentEditedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PostCommentEditSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
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
        $this->bus->dispatch(new PostCommentEditedNotificationMessage($event->comment->getId()));

    }
}
