<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry\DTO;

use App\Entity\Badge;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class EntryBadgeResponseDto implements \JsonSerializable
{
    public ?int $magazineId = null;
    public ?string $name = null;
    public ?int $badgeId = null;

    public function __construct(EntryBadgeDto|Badge $badge)
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
