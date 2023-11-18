<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use App\Entity\User;
use App\Event\Magazine\MagazineSubscribedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class MagazineSubscribe
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine, User $user, $createRequest = true): void
    {
        $user->unblockMagazine($magazine);

        $magazine->subscribe($user);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineSubscribedEvent($magazine, $user));
    }
}
