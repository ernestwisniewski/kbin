<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\EntryCreatedEvent;
use App\Message\EntryCreatedMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EntryCreateSubscriber implements EventSubscriberInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCreatedEvent::class => 'onEntryCreated',
        ];
    }

    public function onEntryCreated(EntryCreatedEvent $event)
    {
        $this->messageBus->dispatch(new EntryCreatedMessage($event->getEntry()->getId()));
    }
}
