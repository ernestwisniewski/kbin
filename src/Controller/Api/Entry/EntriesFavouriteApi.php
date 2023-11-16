<?php

declare(strict_types=1);

namespace App\Controller\Api\Entry;

use App\DTO\EntryResponseDto;
use App\Entity\Entry;
use App\Factory\EntryFactory;
use App\Service\FavouriteManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntriesFavouriteApi extends EntriesBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Entry favourite status toggled',
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
        description: 'The entry to favourite',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'entry')]
    #[Security(name: 'oauth2', scopes: ['entry:vote'])]
    #[IsGranted('ROLE_OAUTH2_ENTRY:VOTE')]
    public function __invoke(
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        FavouriteManager $manager,
        EntryFactory $factory,
        RateLimiterFactory $apiVoteLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiVoteLimiter);

        $manager->toggle($this->getUserOrThrow(), $entry);

        return new JsonResponse(
            $this->serializeEntry($factory->createDto($entry)),
            headers: $headers
        );
    }
}
