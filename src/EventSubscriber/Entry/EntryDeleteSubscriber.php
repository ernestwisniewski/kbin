<?php

declare(strict_types=1);

namespace App\EventSubscriber\Entry;

use App\Event\Entry\EntryBeforePurgeEvent;
use App\Event\Entry\EntryDeletedEvent;
use App\Message\ActivityPub\Outbox\DeleteMessage;
use App\Message\Notification\EntryDeletedNotificationMessage;
use App\Repository\EntryRepository;
use App\Service\ActivityPub\Wrapper\DeleteWrapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\CacheInterface;

class EntryDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly EntryRepository $entryRepository,
        private readonly DeleteWrapper $deleteWrapper,
        private readonly CacheInterface $cache
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

        $this->cache->invalidateTags([
            'entry_'.$event->entry->getId(),
        ]);
    }

    public function onEntryBeforePurge(EntryBeforePurgeEvent $event): void
    {
        $event->entry->magazine->entryCount = $this->entryRepository->countEntriesByMagazine(
            $event->entry->magazine
        ) - 1;

        $this->bus->dispatch(new EntryDeletedNotificationMessage($event->entry->getId()));

        if (!$event->entry->apId) {
            $this->bus->dispatch(
                new DeleteMessage(
                    $this->deleteWrapper->build($event->entry, Uuid::v4()->toRfc4122()),
                    $event->entry->user->getId(),
                    $event->entry->magazine->getId()
                )
            );
        }
    }
}
