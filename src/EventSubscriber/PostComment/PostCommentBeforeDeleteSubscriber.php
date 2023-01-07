<?php

declare(strict_types=1);

namespace App\EventSubscriber\PostComment;

use App\Event\PostComment\PostCommentBeforeDeletedEvent;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PostCommentBeforeDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostCommentBeforeDeletedEvent::class => 'onPostBeforeDelete',
        ];
    }

    public function onPostBeforeDelete(PostCommentBeforeDeletedEvent $event): void
    {
        if (!$event->comment->apId) {
            $this->bus->dispatch(new DeleteMessage($event->comment->getId(), get_class($event->comment)));
        }
    }
}
