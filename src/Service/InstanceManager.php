<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ModeratorDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class InstanceManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function addModerator(ModeratorDto $dto): void
    {
        $dto->user->roles = array_unique(array_merge($dto->user->roles, ['ROLE_MODERATOR']));

        $this->entityManager->persist($dto->user);
        $this->entityManager->flush();
    }

    public function removeModerator(User $user): void
    {
        $user->roles = array_diff($user->roles, ['ROLE_MODERATOR']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
