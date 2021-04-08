<?php declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\DTO\UserProfileSettingsDto;
use App\Entity\User;

class UserProfileSettingsManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function createDto(User $user): UserProfileSettingsDto
    {
        return new UserProfileSettingsDto($user->isNotifyOnNewEntry(), $user->isNotifyOnNewPost());
    }

    public function update(User $user, UserProfileSettingsDto $dto)
    {
        $user->setNotifyOnNewEntry($dto->notifyOnNewEntry);
        $user->setNotifyOnNewPost($dto->notifyOnNewPost);

        $this->entityManager->flush();
    }
}
