<?php declare(strict_types=1);

namespace App\EventSubscriber\Entry;

use App\Event\Entry\EntryBeforeDeletedEvent;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EntryBeforeDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryBeforeDeletedEvent::class => 'onEntryBeforeDeleted',
        ];
    }

    public function onEntryBeforeDeleted(EntryBeforeDeletedEvent $event): void
    {
        if (!$event->entry->apId) {
            $this->bus->dispatch(new DeleteMessage($event->entry->getId(), get_class($event->entry)));
        }
    }
}
