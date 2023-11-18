<?php

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use App\Repository\UserFollowRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserFollowAccept
{
    public function __construct(
        private UserFollowRequestRepository $userFollowRequestRepository,
        private UserFollow $userFollow,
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

        if ($this->userFollowRequestRepository->findOneBy(['follower' => $follower, 'following' => $following])) {
            return;
        }

        ($this->userFollow)($follower, $following, false);
    }
}
