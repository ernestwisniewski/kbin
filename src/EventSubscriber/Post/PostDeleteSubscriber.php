<?php declare(strict_types=1);

namespace App\EventSubscriber\Post;

use App\Event\Post\PostDeletedEvent;
use App\Message\PostDeletedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PostDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostDeletedEvent::class => 'onPostDeleted',
        ];
    }

    public function onPostDeleted(PostDeletedEvent $event)
    {
        $this->bus->dispatch(new PostDeletedNotificationMessage($event->post->getId()));
    }
}
