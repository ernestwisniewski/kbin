<?php

declare(strict_types=1);

namespace App\EventSubscriber\Post;

use App\Event\Post\PostBeforeDeletedEvent;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PostBeforeDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
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
            $this->bus->dispatch(new DeleteMessage($event->post->getId(), get_class($event->post)));
        }
    }
}
