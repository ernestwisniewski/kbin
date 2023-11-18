<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Entry\Admin;

use App\Controller\Api\Entry\EntriesBaseApi;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Kbin\Entry\DTO\EntryResponseDto;
use App\Kbin\Entry\EntryMagazineChange;
use App\Kbin\Entry\Factory\EntryFactory;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntriesChangeMagazineApi extends EntriesBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Entry\'s magazine changed',
        content: new Model(type: EntryResponseDto::class),
        headers: [
            new OA\Header(
                header: 'X-RateLimit-Remaining',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests left until you will be rate limited'
            ),
            new OA\Header(
                header: 'X-RateLimit-Retry-After',
                schema: new OA\Schema(type: 'integer'),
                description: 'Unix timestamp to retry the request after'
            ),
            new OA\Header(
                header: 'X-RateLimit-Limit',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests available'
            ),
        ]
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You are not authorized to change this entry\'s magazine',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Entry not found',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\NotFoundErrorSchema::class))
    )]
    #[OA\Response(
        response: 429,
        description: 'You are being rate limited',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\TooManyRequestsErrorSchema::class)),
        headers: [
            new OA\Header(
                header: 'X-RateLimit-Remaining',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests left until you will be rate limited'
            ),
            new OA\Header(
                header: 'X-RateLimit-Retry-After',
                schema: new OA\Schema(type: 'integer'),
                description: 'Unix timestamp to retry the request after'
            ),
            new OA\Header(
                header: 'X-RateLimit-Limit',
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of requests available'
            ),
        ]
    )]
    #[OA\Parameter(
        name: 'entry_id',
        in: 'path',
        description: 'The entry to move',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'target_id',
        in: 'path',
        description: 'The magazine to move the entry to',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'admin/entry')]
    #[IsGranted('ROLE_ADMIN')]
    #[Security(name: 'oauth2', scopes: ['admin:magazine:move_entry'])]
    #[IsGranted('ROLE_OAUTH2_ADMIN:MAGAZINE:MOVE_ENTRY')]
    /** Changes the magazine of the entry to target */
    public function __invoke(
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        #[MapEntity(id: 'target_id')]
        Magazine $target,
        EntryMagazineChange $entryMagazineChange,
        EntryFactory $factory,
        RateLimiterFactory $apiModerateLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $entryMagazineChange($entry, $target);

        return new JsonResponse(
            $this->serializeEntry($factory->createDto($entry)),
            headers: $headers
        );
    }
}
