<?php

declare(strict_types=1);

namespace App\Kbin\User\UserDeleteRequest;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserDeleteRequestCreate
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user): void
    {
        $user->markedForDeletionAt = null;
        $user->visibility = VisibilityInterface::VISIBILITY_VISIBLE;

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
