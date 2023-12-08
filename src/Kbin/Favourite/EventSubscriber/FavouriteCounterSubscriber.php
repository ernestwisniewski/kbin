<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Favourite\EventSubscriber;

use App\Kbin\Favourite\EventSubscriber\Event\FavouriteEvent;
use App\Repository\FavouriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class FavouriteCounterSubscriber
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FavouriteRepository $favouriteRepository
    ) {
    }

    #[AsEventListener(event: FavouriteEvent::class, priority: -1)]
    public function onFavourite(FavouriteEvent $event): void
    {
        $event->subject->favouriteCount = $this->favouriteRepository->countBySubject($event->subject);
        $event->subject->updateRanking();

        $this->entityManager->flush();
    }
}
