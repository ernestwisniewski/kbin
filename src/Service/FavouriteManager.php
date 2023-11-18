<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Favourite;
use App\Entity\User;
use App\Event\FavouriteEvent;
use App\Factory\FavouriteFactory;
use App\Repository\FavouriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class FavouriteManager
{
    public const TYPE_LIKE = 'like';
    public const TYPE_UNLIKE = 'unlike';

    public function __construct(
        private readonly FavouriteFactory $factory,
        private readonly FavouriteRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public function toggle(User $user, FavouriteInterface $subject, string $type = null): ?Favourite
    {
        if (!($favourite = $this->repository->findBySubject($user, $subject))) {
            if (self::TYPE_UNLIKE === $type) {
                return null;
            }

            $favourite = $this->factory->createFromEntity($user, $subject);
            $this->entityManager->persist($favourite);

            $subject->favourites->add($favourite);
            $subject->updateCounts();
            $subject->updateRanking();
        } else {
            if (self::TYPE_LIKE === $type) {
                return $favourite;
            }

            $subject->favourites->removeElement($favourite);
            $subject->updateCounts();
            $subject->updateRanking();
            $favourite = null;
        }

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new FavouriteEvent($subject, $user, null === $favourite));

        return $favourite ?? null;
    }
}
