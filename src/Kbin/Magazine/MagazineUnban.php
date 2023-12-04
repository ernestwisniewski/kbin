<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use App\Entity\MagazineBan;
use App\Entity\User;
use App\Kbin\Magazine\EventSubscriber\Event\MagazineBanEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class MagazineUnban
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Magazine $magazine, User $user): ?MagazineBan
    {
        if (!$magazine->isBanned($user)) {
            return null;
        }

        $ban = $magazine->unban($user);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new MagazineBanEvent($ban));

        return $ban;
    }
}
