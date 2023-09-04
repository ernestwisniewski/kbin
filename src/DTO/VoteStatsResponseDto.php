<?php

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
