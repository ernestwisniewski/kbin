<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry;

use App\Entity\Entry;
use App\Event\Entry\EntryEditedEvent;
use App\Event\Entry\EntryPinEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class EntryPin
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Entry $entry): Entry
    {
        $entry->sticky = !$entry->sticky;

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryPinEvent($entry));
        $this->eventDispatcher->dispatch(new EntryEditedEvent($entry));

        return $entry;
    }
}
