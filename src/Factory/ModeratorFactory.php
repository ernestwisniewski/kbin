<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ModeratorResponseDto;
use App\Entity\Magazine;
use App\Entity\Moderator;
use App\Entity\User;

class ModeratorFactory
{
    public function __construct(
        private readonly ImageFactory $imageFactory,
    ) {
    }

    public function createDto(Moderator $moderator): ModeratorResponseDto
    {
        return ModeratorResponseDto::create(
            $moderator->magazine->getId(),
            $moderator->user->getId(),
            $moderator->user->username,
            $moderator->user->apId,
            $moderator->user->avatar ? $this->imageFactory->createDto($moderator->user->avatar) : null,
        );
    }

    public function createDtoWithUser(User $user, Magazine $magazine): ModeratorResponseDto
    {
        return ModeratorResponseDto::create(
            $magazine->getId(),
            $user->getId(),
            $user->username,
            $user->apId,
            $user->avatar ? $this->imageFactory->createDto($user->avatar) : null,
        );
    }
}
