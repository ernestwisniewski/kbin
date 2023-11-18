<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use App\Event\User\UserFollowEvent;
use App\Repository\UserFollowRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class UserUnfollow
{
    public function __construct(
        private UserFollowRequestRepository $userFollowRequestRepository,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $follower, User $following): void
    {
        if ($request = $this->userFollowRequestRepository->findOneby(
            ['follower' => $follower, 'following' => $following]
        )) {
            $this->entityManager->remove($request);
        }

        $follower->unfollow($following);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserFollowEvent($follower, $following, true));
    }
}
