<?php

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class MagazineSmallResponseDto implements \JsonSerializable
{
    public ?string $name = null;
    public ?int $magazineId = null;
    public ?ImageDto $icon = null;
    public ?bool $isUserSubscribed = null;
    public ?bool $isBlockedByUser = null;
    public ?string $apId = null;
    public ?string $apProfileId = null;

    public function __construct(MagazineDto $dto)
    {
        $this->name = $dto->name;
        $this->magazineId = $dto->getId();
        $this->icon = $dto->icon;
        $this->isUserSubscribed = $dto->isUserSubscribed;
        $this->isBlockedByUser = $dto->isBlockedByUser;
        $this->apId = $dto->apId;
        $this->apProfileId = $dto->apProfileId;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'magazineId' => $this->magazineId,
            'name' => $this->name,
            'icon' => $this->icon,
            'isUserSubscribed' => $this->isUserSubscribed,
            'isBlockedByUser' => $this->isBlockedByUser,
            'apId' => $this->apId,
            'apProfileId' => $this->apProfileId,
        ];
    }
}
