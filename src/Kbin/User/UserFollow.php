<?php

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use App\Entity\UserFollowRequest;
use App\Event\User\UserFollowEvent;
use App\Repository\UserFollowRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class UserFollow
{
    public function __construct(
        private UserFollowRequestRepository $userFollowRequestRepository,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $follower, User $following, $createRequest = true): void
    {
        if ($following->apManuallyApprovesFollowers && $createRequest) {
            if ($this->userFollowRequestRepository->findOneby(['follower' => $follower, 'following' => $following])) {
                return;
            }

            $request = new UserFollowRequest($follower, $following);
            $this->entityManager->persist($request);
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new UserFollowEvent($follower, $following));

            return;
        }

        $follower->unblock($following);

        $follower->follow($following);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserFollowEvent($follower, $following));
    }
}
