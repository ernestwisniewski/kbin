<?php

declare(strict_types=1);

namespace App\Controller\Api\Magazine;

use App\DTO\MagazineResponseDto;
use App\Entity\Magazine;
use App\Factory\MagazineFactory;
use App\Kbin\Magazine\MagazineBlock;
use App\Kbin\Magazine\MagazineUnblock;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineBlockApi extends MagazineBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Magazine blocked',
        content: new Model(type: MagazineResponseDto::class),
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
        description: 'Magazine not found',
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
        name: 'magazine_id',
        in: 'path',
        description: 'The magazine to block',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'magazine')]
    #[Security(name: 'oauth2', scopes: ['magazine:block'])]
    #[IsGranted('ROLE_OAUTH2_MAGAZINE:BLOCK')]
    #[IsGranted('block', subject: 'magazine')]
    public function block(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        MagazineBlock $magazineBlock,
        MagazineFactory $factory,
        RateLimiterFactory $apiUpdateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiUpdateLimiter);

        $magazineBlock($magazine, $this->getUserOrThrow());

        return new JsonResponse(
            $this->serializeMagazine($factory->createDto($magazine)),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Magazine unblocked',
        content: new Model(type: MagazineResponseDto::class),
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
        description: 'Magazine not found',
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
        name: 'magazine_id',
        in: 'path',
        description: 'The magazine to unblock',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'magazine')]
    #[Security(name: 'oauth2', scopes: ['magazine:block'])]
    #[IsGranted('ROLE_OAUTH2_MAGAZINE:BLOCK')]
    #[IsGranted('block', subject: 'magazine')]
    public function unblock(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        MagazineUnblock $magazineUnblock,
        MagazineFactory $factory,
        RateLimiterFactory $apiUpdateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiUpdateLimiter);

        $magazineUnblock($magazine, $this->getUserOrThrow());

        return new JsonResponse(
            $this->serializeMagazine($factory->createDto($magazine)),
            headers: $headers
        );
    }
}
