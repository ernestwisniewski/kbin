<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\EntryCreatedEvent;
use App\Event\PostCreatedEvent;
use App\Message\EntryCreatedMessage;
use App\Message\PostCreatedMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PostCreateSubscriber implements EventSubscriberInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostCreatedEvent::class => 'onPostCreated',
        ];
    }

    public function onPostCreated(PostCreatedEvent $event)
    {
        $this->messageBus->dispatch(new PostCreatedMessage($event->getPost()->getId()));
    }
}
