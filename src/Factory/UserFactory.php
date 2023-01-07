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
            $user->fields,
            $user->apId,
            $user->apProfileId,
            $user->getId(),
        );
    }

    public function createDtoFromAp($apProfileId, $apId): UserDto
    {
        $dto = (new UserDto())->create('@'.$apId, $apId, null, null, null, null, $apId, $apProfileId);
        $dto->plainPassword = bin2hex(random_bytes(20));

        return $dto;
    }
}
