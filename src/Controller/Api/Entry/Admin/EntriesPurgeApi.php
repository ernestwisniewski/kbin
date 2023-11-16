<?php

declare(strict_types=1);

namespace App\Controller\Api\Entry\Admin;

use App\Controller\Api\Entry\EntriesBaseApi;
use App\Entity\Entry;
use App\Kbin\Entry\EntryPurge;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntriesPurgeApi extends EntriesBaseApi
{
    #[OA\Response(
        response: 204,
        description: 'Entry purged',
        content: null,
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
        description: 'You are not authorized to purge this entry',
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
        description: 'The entry to purge',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'admin/entry')]
    #[IsGranted('ROLE_ADMIN')]
    #[Security(name: 'oauth2', scopes: ['admin:entry:purge'])]
    #[IsGranted('ROLE_OAUTH2_ADMIN:ENTRY:PURGE')]
    #[IsGranted('purge', subject: 'entry')]
    /**
     * Purges an entry from the instance, deleting it completely. This action is irreversible.
     */
    public function __invoke(
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        EntryPurge $entryPurge,
        RateLimiterFactory $apiModerateLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $entryPurge($this->getUserOrThrow(), $entry);

        return new JsonResponse(
            status: 204,
            headers: $headers
        );
    }
}
