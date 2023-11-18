<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User\Factory;

use App\Entity\User;
use App\Factory\ImageFactory;
use App\Kbin\User\DTO\UserDto;
use App\Kbin\User\DTO\UserSmallResponseDto;
use Symfony\Bundle\SecurityBundle\Security;

readonly class UserFactory
{
    public function __construct(
        private ImageFactory $imageFactory,
        private Security $security,
    ) {
    }

    public function createDto(User $user): UserDto
    {
        $dto = UserDto::create(
            $user->username,
            $user->email,
            $user->avatar ? $this->imageFactory->createDto($user->avatar) : null,
            $user->cover ? $this->imageFactory->createDto($user->cover) : null,
            $user->about,
            $user->createdAt,
            $user->fields,
            $user->apId,
            $user->apProfileId,
            $user->getId(),
            $user->followersCount,
            $user->isBot
        );

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        // Only return the user's vote if permission to control voting has been given
        $dto->isFollowedByUser = $this->security->isGranted('ROLE_OAUTH2_USER:FOLLOW') ? $currentUser->isFollowing($user) : null;
        $dto->isFollowerOfUser = $this->security->isGranted('ROLE_OAUTH2_USER:FOLLOW') && $user->showProfileFollowings ? $user->isFollowing($currentUser) : null;
        $dto->isBlockedByUser = $this->security->isGranted('ROLE_OAUTH2_USER:BLOCK') ? $currentUser->isBlocked($user) : null;

        return $dto;
    }

    public function createSmallDto(User|UserDto $user): UserSmallResponseDto
    {
        $dto = $user instanceof User ? $this->createDto($user) : $user;

        return new UserSmallResponseDto($dto);
    }

    public function createDtoFromAp($apProfileId, $apId): UserDto
    {
        $dto = (new UserDto())->create('@'.$apId, $apId, null, null, null, null, null, $apId, $apProfileId);
        $dto->plainPassword = bin2hex(random_bytes(20));

        return $dto;
    }
}
