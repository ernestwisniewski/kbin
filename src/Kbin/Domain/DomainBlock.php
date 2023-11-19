<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Domain;

use App\Entity\Domain;
use App\Entity\User;
use App\Event\DomainBlockedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class DomainBlock
{
    public function __construct(
        private DomainUnsubscribe $domainUnsubscribe,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Domain $domain, User $user): void
    {
        ($this->domainUnsubscribe)($domain, $user);

        $user->blockDomain($domain);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new DomainBlockedEvent($domain, $user));
    }
}
