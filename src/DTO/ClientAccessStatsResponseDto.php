<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class ClientAccessStatsResponseDto
{
    public ?string $client = null;
    #[OA\Property(description: "Timestamp of form 'YYYY-MM-DD HH:MM:SS' in UTC")]
    public ?string $datetime = null;
    public ?int $count = null;
}
