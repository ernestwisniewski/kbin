<?php

declare(strict_types=1);

namespace App\Schema;

use App\DTO\MagazineResponseDto;
use App\DTO\UserResponseDto;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'object',
    properties: [
        new OA\Property('type', example: 'string', type: 'string', enum: SearchActorSchema::TYPES),
        new OA\Property('object', type: 'object', oneOf: [
            new OA\Schema(ref: new Model(type: MagazineResponseDto::class)),
            new OA\Schema(ref: new Model(type: UserResponseDto::class)),
        ]),
    ]
)]
class SearchActorSchema
{
    public const TYPES = [
        'user',
        'magazine',
    ];
}
