<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\User;
use App\Kbin\Contract\DeleteContentServiceInterface;
use App\Kbin\Entry\EventSubscriber\Event\EntryBeforeDeletedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryDeletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class EntryDelete implements DeleteContentServiceInterface
{
    public function __construct(
        private EntryPurge $entryPurge,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(User $user, ContentInterface|Entry $subject): void
    {
        if ($user->apDomain && $user->apDomain !== parse_url($subject->apId, PHP_URL_HOST)) {
            return;
        }

        if ($subject->isAuthor($user) && $subject->comments->isEmpty()) {
            ($this->entryPurge)($user, $subject);

            return;
        }

        $subject->isAuthor($user) ? $subject->softDelete() : $subject->trash();

        $this->eventDispatcher->dispatch(new EntryBeforeDeletedEvent($subject, $user));

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryDeletedEvent($subject, $user));
    }
}
