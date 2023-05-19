<?php

declare(strict_types=1);

namespace App\EventSubscriber\Post;

use App\Event\Post\PostBeforePurgeEvent;
use App\Event\Post\PostDeletedEvent;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Message\Notification\PostDeletedNotificationMessage;
use App\Repository\MagazineRepository;
use App\Repository\PostRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PostDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly PostRepository $postRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostDeletedEvent::class => 'onPostDeleted',
            PostBeforePurgeEvent::class => 'onPostBeforePurge',
        ];
    }

    public function onPostDeleted(PostDeletedEvent $event)
    {
        $this->bus->dispatch(new PostDeletedNotificationMessage($event->post->getId()));
    }

    public function onPostBeforePurge(PostBeforePurgeEvent $event): void
    {
        $event->post->magazine->postCount = $this->postRepository->countPostsByMagazine($event->post->magazine) - 1;

        $this->bus->dispatch(new PostDeletedNotificationMessage($event->post->getId()));

        if (!$event->post->apId) {
            $this->bus->dispatch(new DeleteMessage($event->post->getId(), get_class($event->post)));
        }
    }
}
