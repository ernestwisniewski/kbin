<?php

declare(strict_types=1);

namespace App\Schema;

use App\DTO\EntryCommentResponseDto;
use App\DTO\EntryResponseDto;
use App\DTO\PostCommentResponseDto;
use App\DTO\PostResponseDto;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'object',
    anyOf: [
        new OA\Schema(ref: new Model(type: EntryResponseDto::class)),
        new OA\Schema(ref: new Model(type: EntryCommentResponseDto::class)),
        new OA\Schema(ref: new Model(type: PostResponseDto::class)),
        new OA\Schema(ref: new Model(type: PostCommentResponseDto::class)),
    ],
    properties: [
        new OA\Property('itemType', example: 'string', type: 'string', enum: ContentSchema::TYPES),
    ]
)]
class ContentSchema
{
    public const TYPES = [
        'entry',
        'entry_comment',
        'post',
        'post_comment',
    ];
}
