<?php

declare(strict_types=1);

namespace App\Schema;

use App\Kbin\Entry\DTO\EntryResponseDto;
use App\Kbin\EntryComment\DTO\EntryCommentResponseDto;
use App\Kbin\Post\DTO\PostResponseDto;
use App\Kbin\PostComment\DTO\PostCommentResponseDto;
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
