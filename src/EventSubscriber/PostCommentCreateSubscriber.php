<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\PostCommentCreatedEvent;
use App\Message\PostCommentNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PostCommentCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
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
        $this->bus->dispatch(new PostCommentNotificationMessage($event->comment->getId()));

    }
}
