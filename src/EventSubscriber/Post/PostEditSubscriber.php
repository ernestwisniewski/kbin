<?php declare(strict_types=1);

namespace App\EventSubscriber\Post;

use App\Event\Post\PostEditedEvent;
use App\Message\Notification\PostEditedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PostEditSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostEditedEvent::class => 'onPostEdited',
        ];
    }

    public function onPostEdited(PostEditedEvent $event)
    {
        $this->bus->dispatch(new PostEditedNotificationMessage($event->post->getId()));
    }
}
