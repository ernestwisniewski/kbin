<?php

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class UserSmallResponseDto implements \JsonSerializable
{
    public ?int $userId = null;
    public ?string $username = null;
    public ?bool $isBot = null;
    public ?bool $isFollowedByUser = null;
    public ?bool $isFollowerOfUser = null;
    public ?bool $isBlockedByUser = null;
    public ?ImageDto $avatar = null;
    public ?string $apId = null;
    public ?string $apProfileId = null;
    public ?\DateTimeImmutable $createdAt = null;

    public function __construct(UserDto $dto)
    {
        $this->userId = $dto->getId();
        $this->username = $dto->username;
        $this->isBot = $dto->isBot;
        $this->isFollowedByUser = $dto->isFollowedByUser;
        $this->isFollowerOfUser = $dto->isFollowerOfUser;
        $this->isBlockedByUser = $dto->isBlockedByUser;
        $this->avatar = $dto->avatar;
        $this->apId = $dto->apId;
        $this->apProfileId = $dto->apProfileId;
        $this->createdAt = $dto->createdAt;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'userId' => $this->userId,
            'username' => $this->username,
            'isBot' => $this->isBot,
            'isFollowedByUser' => $this->isFollowedByUser,
            'isFollowerOfUser' => $this->isFollowerOfUser,
            'isBlockedByUser' => $this->isBlockedByUser,
            'avatar' => $this->avatar,
            'apId' => $this->apId,
            'apProfileId' => $this->apProfileId,
            'createdAt' => $this->createdAt?->format(\DateTimeImmutable::ATOM),
        ];
    }
}
