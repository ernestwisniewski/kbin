<?php

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use App\Event\User\UserBlockEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserBlock
{
    public function __construct(
        private UserUnfollow $userUnfollow,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $blocker, User $blocked): void
    {
        ($this->userUnfollow)($blocker, $blocked);

        $blocker->block($blocked);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new UserBlockEvent($blocker, $blocked));
    }
}
