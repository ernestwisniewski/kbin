<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\UserProfileSettingsDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserProfileSettingsManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function createDto(User $user): UserProfileSettingsDto
    {
        return new UserProfileSettingsDto($user->notifyOnNewEntry, $user->notifyOnNewPost);
    }

    public function update(User $user, UserProfileSettingsDto $dto)
    {
        $user->notifyOnNewPost  = $dto->notifyOnNewPost;
        $user->notifyOnNewEntry = $dto->notifyOnNewEntry;

        $this->entityManager->flush();
    }
}
