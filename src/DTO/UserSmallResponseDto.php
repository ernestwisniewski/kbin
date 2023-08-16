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
    public ?ImageDto $avatar = null;
    public ?string $apId = null;
    public ?string $apProfileId = null;

    public function __construct(UserDto $dto)
    {
        $this->userId = $dto->getId();
        $this->username = $dto->username;
        $this->isBot = $dto->isBot;
        $this->avatar = $dto->avatar;
        $this->apId = $dto->apId;
        $this->apProfileId = $dto->apProfileId;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'userId' => $this->userId,
            'username' => $this->username,
            'isBot' => $this->isBot,
            'avatar' => $this->avatar?->jsonSerialize(),
            'apId' => $this->apId,
            'apProfileId' => $this->apProfileId,
        ];
    }
}
