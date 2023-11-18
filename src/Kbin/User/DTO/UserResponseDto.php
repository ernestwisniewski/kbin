<?php

declare(strict_types=1);

namespace App\Kbin\User\DTO;

use App\DTO\ImageDto;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class UserResponseDto implements \JsonSerializable
{
    public ?ImageDto $avatar = null;
    public ?ImageDto $cover = null;
    public string $username;
    public int $followersCount = 0;
    public ?string $about = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?string $apProfileId = null;
    public ?string $apId = null;
    public ?bool $isBot = null;
    public ?bool $isFollowedByUser = null;
    public ?bool $isFollowerOfUser = null;
    public ?bool $isBlockedByUser = null;
    public ?int $userId = null;

    public function __construct(UserDto $dto)
    {
        $this->userId = $dto->getId();
        $this->username = $dto->username;
        $this->about = $dto->about;
        $this->avatar = $dto->avatar;
        $this->cover = $dto->cover;
        $this->createdAt = $dto->createdAt;
        $this->apId = $dto->apId;
        $this->apProfileId = $dto->apProfileId;
        $this->followersCount = $dto->followersCount;
        $this->isBot = true === $dto->isBot;
        $this->isFollowedByUser = $dto->isFollowedByUser;
        $this->isFollowerOfUser = $dto->isFollowerOfUser;
        $this->isBlockedByUser = $dto->isBlockedByUser;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'userId' => $this->userId,
            'username' => $this->username,
            'about' => $this->about,
            'avatar' => $this->avatar?->jsonSerialize(),
            'cover' => $this->cover?->jsonSerialize(),
            'createdAt' => $this->createdAt?->format(\DateTimeInterface::ATOM),
            'followersCount' => $this->followersCount,
            'apId' => $this->apId,
            'apProfileId' => $this->apProfileId,
            'isBot' => $this->isBot,
            'isFollowedByUser' => $this->isFollowedByUser,
            'isFollowerOfUser' => $this->isFollowerOfUser,
            'isBlockedByUser' => $this->isBlockedByUser,
        ];
    }
}
