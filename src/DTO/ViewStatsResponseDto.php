<?php

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class ViewStatsResponseDto
{
    public ?string $datetime = null;
    public ?int $count = null;
}
