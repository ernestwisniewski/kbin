<?php

declare(strict_types=1);

namespace App\Controller\Api\Entry\Moderate;

use App\Controller\Api\Entry\EntriesBaseApi;
use App\DTO\EntryResponseDto;
use App\Entity\Entry;
use App\Factory\EntryFactory;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntriesSetAdultApi extends EntriesBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Entry isAdult status set',
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
        description: 'You are not authorized to moderate this entry',
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
        description: 'The entry to set adult status on',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'adult',
        in: 'path',
        description: 'new isAdult status',
        schema: new OA\Schema(type: 'boolean', default: true),
    )]
    #[OA\Tag(name: 'moderation/entry')]
    #[Security(name: 'oauth2', scopes: ['moderate:entry:set_adult'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:ENTRY:SET_ADULT')]
    #[IsGranted('moderate', subject: 'entry')]
    public function __invoke(
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        EntityManagerInterface $manager,
        EntryFactory $factory,
        RateLimiterFactory $apiModerateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $request = $this->request->getCurrentRequest();
        // Returns true for "1", "true", "on" and "yes". Returns false otherwise.
        $entry->isAdult = filter_var($request->get('adult', 'true'), FILTER_VALIDATE_BOOL);

        $manager->flush();

        return new JsonResponse(
            $this->serializeEntry($factory->createDto($entry)),
            headers: $headers
        );
    }
}
