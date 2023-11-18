<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use App\Event\User\UserBlockEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class UserUnblock
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $blocker, User $blocked): void
    {
        $blocker->unblock($blocked);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserBlockEvent($blocker, $blocked));
    }
}
