<?php

declare(strict_types=1);

namespace App\Controller\Api\Domain;

use App\DTO\DomainDto;
use App\Entity\Domain;
use App\Factory\DomainFactory;
use App\Service\DomainManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DomainSubscribeApi extends DomainBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Domain subscription status updated',
        content: new Model(type: DomainDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Domain not found',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\NotFoundErrorSchema::class))
    )]
    #[OA\Response(
        response: 429,
        description: 'You are being rate limited',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\TooManyRequestsErrorSchema::class)),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'domain_id',
        in: 'path',
        description: 'The domain to subscribe to',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'domain')]
    #[Security(name: 'oauth2', scopes: ['domain:subscribe'])]
    #[IsGranted('ROLE_OAUTH2_DOMAIN:SUBSCRIBE')]
    public function subscribe(
        #[MapEntity(id: 'domain_id')]
        Domain $domain,
        DomainManager $manager,
        DomainFactory $factory,
        RateLimiterFactory $apiUpdateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiUpdateLimiter);

        $manager->subscribe($domain, $this->getUserOrThrow());

        return new JsonResponse(
            $this->serializeDomain($factory->createDto($domain)),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Domain subscription status updated',
        content: new Model(type: DomainDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Domain not found',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\NotFoundErrorSchema::class))
    )]
    #[OA\Response(
        response: 429,
        description: 'You are being rate limited',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\TooManyRequestsErrorSchema::class)),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'domain_id',
        in: 'path',
        description: 'The domain to unsubscribe from',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'domain')]
    #[Security(name: 'oauth2', scopes: ['domain:subscribe'])]
    #[IsGranted('ROLE_OAUTH2_DOMAIN:SUBSCRIBE')]
    public function unsubscribe(
        #[MapEntity(id: 'domain_id')]
        Domain $domain,
        DomainManager $manager,
        DomainFactory $factory,
        RateLimiterFactory $apiUpdateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiUpdateLimiter);

        $manager->unsubscribe($domain, $this->getUserOrThrow());

        return new JsonResponse(
            $this->serializeDomain($factory->createDto($domain)),
            headers: $headers
        );
    }
}
