<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\UserSettingsDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserSettingsManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function createDto(User $user): UserSettingsDto
    {
        return new UserSettingsDto($user->notifyOnNewEntry, $user->notifyOnNewPost);
    }

    public function update(User $user, UserSettingsDto $dto)
    {
        $user->notifyOnNewPost  = $dto->notifyOnNewPost;
        $user->notifyOnNewEntry = $dto->notifyOnNewEntry;

        $this->entityManager->flush();
    }
}
