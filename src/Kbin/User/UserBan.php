<?php

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use App\Exception\UserCannotBeBanned;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserBan
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user): void
    {
        if ($user->isAdmin() || $user->isModerator()) {
            throw new UserCannotBeBanned();
        }

        $user->isBanned = true;

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
