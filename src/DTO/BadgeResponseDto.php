<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Badge;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class BadgeResponseDto implements \JsonSerializable
{
    public ?int $magazineId = null;
    public ?string $name = null;
    public ?int $badgeId = null;

    public function __construct(BadgeDto|Badge $badge)
    {
        $this->magazineId = $badge->magazine->getId();
        $this->name = $badge->name;
        $this->badgeId = $badge->getId();
    }

    public function jsonSerialize(): mixed
    {
        return [
            'magazineId' => $this->magazineId,
            'name' => $this->name,
            'badgeId' => $this->badgeId,
        ];
    }
}
