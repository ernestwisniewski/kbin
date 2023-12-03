<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry;

use App\Entity\Entry;
use App\Kbin\Entry\EventSubscriber\Event\EntryEditedEvent;
use App\Kbin\MessageBus\ImagePurgeMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class EntryImageDetach
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Entry $entry): void
    {
        $image = $entry->image->filePath;

        $entry->image = null;

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new ImagePurgeMessage($image));

        $this->eventDispatcher->dispatch(new EntryEditedEvent($entry));
    }
}
