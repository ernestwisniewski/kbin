<?php declare(strict_types=1);

namespace App\EventSubscriber\Entry;

use App\Event\Entry\EntryBeforePurgeEvent;
use App\Event\Entry\EntryDeletedEvent;
use App\Message\Notification\EntryDeletedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EntryDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryDeletedEvent::class     => 'onEntryDeleted',
            EntryBeforePurgeEvent::class => 'onEntryBeforePurge',
        ];
    }

    public function onEntryDeleted(EntryDeletedEvent $event): void
    {
        $this->bus->dispatch(new EntryDeletedNotificationMessage($event->entry->getId()));
    }

    public function onEntryBeforePurge(EntryBeforePurgeEvent $event): void
    {
        $this->bus->dispatch(new EntryDeletedNotificationMessage($event->entry->getId()));
    }
}
