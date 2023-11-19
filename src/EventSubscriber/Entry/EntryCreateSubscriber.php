<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber\Entry;

use App\Event\Entry\EntryCreatedEvent;
use App\Kbin\Domain\DomainExtract;
use App\Kbin\Entry\MessageBus\EntryEmbedAttachMessage;
use App\Kbin\MessageBus\LinkEmbedMessage;
use App\Message\ActivityPub\Outbox\CreateMessage;
use App\Message\Notification\EntryCreatedNotificationMessage;
use App\Repository\EntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class EntryCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $bus,
        private DomainExtract $domainExtract,
        private EntryRepository $entryRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryCreatedEvent::class => 'onEntryCreated',
        ];
    }

    public function onEntryCreated(EntryCreatedEvent $event): void
    {
        $event->entry->magazine->entryCount = $this->entryRepository->countEntriesByMagazine($event->entry->magazine);

        $this->entityManager->flush();

        ($this->domainExtract)($event->entry);
        $this->bus->dispatch(new EntryEmbedAttachMessage($event->entry->getId()));
        $this->bus->dispatch(new EntryCreatedNotificationMessage($event->entry->getId()));
        if ($event->entry->body) {
            $this->bus->dispatch(new LinkEmbedMessage($event->entry->body));
        }

        if (!$event->entry->apId) {
            $this->bus->dispatch(new CreateMessage($event->entry->getId(), \get_class($event->entry)));
        }
    }
}
