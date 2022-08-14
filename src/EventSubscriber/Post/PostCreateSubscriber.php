<?php declare(strict_types=1);

namespace App\EventSubscriber\Post;

use App\Event\Post\PostCreatedEvent;
use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\Notification\PostCreatedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PostCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostCreatedEvent::class => 'onPostCreated',
        ];
    }

    public function onPostCreated(PostCreatedEvent $event)
    {
        $this->bus->dispatch(new PostCreatedNotificationMessage($event->post->getId()));

        if (!$event->post->apId) {
            $this->bus->dispatch(new CreateMessage($event->post->getId(), get_class($event->post)));
        }
    }
}
