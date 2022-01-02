<?php declare(strict_types=1);

namespace App\EventSubscriber\Entry;

use App\Event\Entry\EntryEditedEvent;
use App\Message\Notification\EntryEditedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EntryEditSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryEditedEvent::class => 'onEntryEdited',
        ];
    }

    public function onEntryEdited(EntryEditedEvent $event): void
    {
        $this->bus->dispatch(new EntryEditedNotificationMessage($event->entry->getId()));
    }
}
