<?php

declare(strict_types=1);

namespace App\Schema\Errors;

use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'object',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'https://tools.ietf.org/html/rfc2616#section-10'),
        new OA\Property(property: 'title', type: 'string', example: 'An error occurred'),
        new OA\Property(property: 'status', type: 'integer', example: 401),
        new OA\Property(property: 'detail', type: 'string', example: 'Unauthorized'),
    ]
)]
class UnauthorizedErrorSchema
{
}
