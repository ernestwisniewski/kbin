<?php

declare(strict_types=1);

namespace App\EventSubscriber\PostComment;

use App\Event\PostComment\PostCommentBeforeDeletedEvent;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Service\ActivityPub\Wrapper\DeleteWrapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class PostCommentBeforeDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly DeleteWrapper       $deleteWrapper,
    )
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
            $this->bus->dispatch(
                new DeleteMessage(
                    $this->deleteWrapper->build($event->comment, Uuid::v4()->toRfc4122()),
                    $event->comment->user->getId(),
                    $event->comment->magazine->getId()
                )
            );
        }
    }
}
