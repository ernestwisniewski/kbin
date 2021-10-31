<?php declare(strict_types = 1);

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
            $user->getId(),
        );
    }
}
