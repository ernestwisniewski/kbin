<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry;

use App\Entity\Entry;
use App\Entity\User;
use App\Kbin\Entry\EventSubscriber\Event\EntryBeforeDeletedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryDeletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class EntryTrash
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(User $user, Entry $entry): void
    {
        $entry->trash();

        $this->eventDispatcher->dispatch(new EntryBeforeDeletedEvent($entry, $user));

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryDeletedEvent($entry, $user));
    }
}
