<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine\DTO;

use App\DTO\ImageDto;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class MagazineModeratorResponseDto implements \JsonSerializable
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
        $dto = new MagazineModeratorResponseDto();
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
