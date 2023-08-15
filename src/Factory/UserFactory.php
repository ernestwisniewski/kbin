<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\UserDto;
use App\Entity\User;

class UserFactory
{
    public function createDto(User $user): UserDto
    {
        return (new UserDto())->create(
            $user->username,
            $user->email,
            $user->avatar,
            $user->cover,
            $user->about,
            $user->lastActive,
            $user->fields,
            $user->apId,
            $user->apProfileId,
            $user->getId(),
            $user->followersCount,
            $user->isBot
        );
    }

    public function createDtoFromAp($apProfileId, $apId): UserDto
    {
        $dto = (new UserDto())->create(username: '@'.$apId, email: $apId, apId: $apId, apProfileId: $apProfileId);
        $dto->plainPassword = bin2hex(random_bytes(20));

        return $dto;
    }
}
