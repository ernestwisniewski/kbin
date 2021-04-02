<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\PostCreatedEvent;
use App\Message\PostNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PostCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $messageBus)
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
        $this->messageBus->dispatch(new PostNotificationMessage($event->getPost()->getId()));
    }
}
