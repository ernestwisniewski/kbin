<?php

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
