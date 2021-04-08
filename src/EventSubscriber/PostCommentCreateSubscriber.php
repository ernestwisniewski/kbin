<?php declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\PostCommentNotificationMessage;
use App\Event\PostCommentCreatedEvent;

class PostCommentCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $messageBus)
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
        $this->messageBus->dispatch(new PostCommentNotificationMessage($event->comment->getId()));

    }
}
