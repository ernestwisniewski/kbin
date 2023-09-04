<?php

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class ModeratorResponseDto implements \JsonSerializable
{
    public ?int $magazineId = null;
    public ?int $userId = null;
    public ?ImageDto $avatar = null;
    public ?string $username = null;
    public ?string $apId = null;

    public static function create(
        int $magazineId = null,
        int $userId = null,
        string $username = null,
        string $apId = null,
        ImageDto $avatar = null
    ): self {
        $dto = new ModeratorResponseDto();
        $dto->magazineId = $magazineId;
        $dto->userId = $userId;
        $dto->avatar = $avatar;
        $dto->username = $username;
        $dto->apId = $apId;

        return $dto;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'magazineId' => $this->magazineId,
            'userId' => $this->userId,
            'avatar' => $this->avatar?->jsonSerialize(),
            'username' => $this->username,
            'apId' => $this->apId,
        ];
    }
}
