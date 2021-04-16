<?php declare(strict_types=1);

namespace App\EventSubscriber\PostComment;

use App\Event\PostComment\PostCommentDeletedEvent;
use App\Message\EntryCommentDeletedNotificationMessage;
use App\Message\PostCommentDeletedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PostCommentDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostCommentDeletedEvent::class => 'onPostCommentDeleted',
        ];
    }

    public function onPostCommentDeleted(PostCommentDeletedEvent $event): void
    {
        $this->bus->dispatch(new PostCommentDeletedNotificationMessage($event->comment->getId()));
    }
}
