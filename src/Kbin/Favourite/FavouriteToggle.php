<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Favourite;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\Contracts\VotableInterface;
use App\Entity\Favourite;
use App\Entity\User;
use App\Factory\FavouriteFactory;
use App\Kbin\Favourite\EventSubscriber\Event\FavouriteEvent;
use App\Repository\FavouriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class FavouriteToggle
{
    public const TYPE_LIKE = 'like';
    public const TYPE_UNLIKE = 'unlike';

    public function __construct(
        private readonly FavouriteFactory $factory,
        private readonly FavouriteRepository $repository,
        private readonly RateLimiterFactory $spamProtectionLimiter,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public function __invoke(User $user, FavouriteInterface|VotableInterface $subject, string $type = null, bool $rateLimit = true): ?Favourite
    {
        if($rateLimit) {
            $spamProtection = $this->spamProtectionLimiter->create((string)$user->getId());
            if (false === $spamProtection->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        if (!($favourite = $this->repository->findBySubject($user, $subject))) {
            if (self::TYPE_UNLIKE === $type) {
                return null;
            }

            $favourite = $this->factory->createFromEntity($user, $subject);
            $this->entityManager->persist($favourite);

            $subject->updateRanking();
        } else {
            if (self::TYPE_LIKE === $type) {
                return $favourite;
            }

            $this->entityManager->remove($favourite);
            $subject->updateRanking();
            $favourite = null;
        }

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new FavouriteEvent($subject, $user, null === $favourite));

        return $favourite ?? null;
    }
}
