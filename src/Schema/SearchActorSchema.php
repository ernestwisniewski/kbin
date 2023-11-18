<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Schema;

use App\Kbin\Magazine\DTO\MagazineResponseDto;
use App\Kbin\User\DTO\UserResponseDto;
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
