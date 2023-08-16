<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\UserDto;
use App\DTO\UserSmallResponseDto;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class UserFactory
{
    public function __construct(
        private readonly ImageFactory $imageFactory,
        private readonly Security $security,
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
