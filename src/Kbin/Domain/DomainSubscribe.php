<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Domain;

use App\Entity\Domain;
use App\Entity\User;
use App\Kbin\Domain\EventSubscriber\Event\DomainSubscribedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class DomainSubscribe
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Domain $domain, User $user): void
    {
        $user->unblockDomain($domain);

        $domain->subscribe($user);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new DomainSubscribedEvent($domain, $user));
    }
}
