<?php

declare(strict_types=1);

namespace App\EventSubscriber\Post;

use App\Event\Post\PostBeforeDeletedEvent;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Service\ActivityPub\Wrapper\DeleteWrapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class PostBeforeDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly DeleteWrapper $deleteWrapper
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostBeforeDeletedEvent::class => 'onPostBeforeDelete',
        ];
    }

    public function onPostBeforeDelete(PostBeforeDeletedEvent $event): void
    {
        if (!$event->post->apId) {
            $this->bus->dispatch(
                new DeleteMessage(
                    $this->deleteWrapper->build($event->post, Uuid::v4()->toRfc4122()),
                    $event->post->user->getId(),
                    $event->post->magazine->getId()
                )
            );
        }
    }
}
