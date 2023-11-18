<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class VoteStatsResponseDto
{
    public ?string $datetime = null;
    public ?int $boost = null;
    public ?int $down = null;
    public ?int $up = null;
}
