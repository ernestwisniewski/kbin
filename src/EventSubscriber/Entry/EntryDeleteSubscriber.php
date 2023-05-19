<?php

declare(strict_types=1);

namespace App\EventSubscriber\Entry;

use App\Event\Entry\EntryBeforePurgeEvent;
use App\Event\Entry\EntryDeletedEvent;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Message\Notification\EntryDeletedNotificationMessage;
use App\Repository\EntryRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EntryDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly EntryRepository $entryRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryDeletedEvent::class => 'onEntryDeleted',
            EntryBeforePurgeEvent::class => 'onEntryBeforePurge',
        ];
    }

    public function onEntryDeleted(EntryDeletedEvent $event): void
    {
        $this->bus->dispatch(new EntryDeletedNotificationMessage($event->entry->getId()));
    }

    public function onEntryBeforePurge(EntryBeforePurgeEvent $event): void
    {
        $event->entry->magazine->entryCount = $this->entryRepository->countEntriesByMagazine(
                $event->entry->magazine
            ) - 1;

        $this->bus->dispatch(new EntryDeletedNotificationMessage($event->entry->getId()));

        if (!$event->entry->apId) {
            $this->bus->dispatch(new DeleteMessage($event->entry->getId(), get_class($event->entry)));
        }
    }
}
