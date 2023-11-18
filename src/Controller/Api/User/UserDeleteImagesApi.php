<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Kbin\User\DTO\UserResponseDto;
use App\Kbin\User\Factory\UserFactory;
use App\Kbin\User\UserAvatarDetach;
use App\Kbin\User\UserCoverDetach;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserDeleteImagesApi extends UserBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'User avatar deleted',
        content: new Model(type: UserResponseDto::class),
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
    #[OA\Tag(name: 'user')]
    #[Security(name: 'oauth2', scopes: ['user:profile:edit'])]
    #[IsGranted('ROLE_OAUTH2_USER:PROFILE:EDIT')]
    public function avatar(
        UserAvatarDetach $userAvatarDetach,
        UserFactory $factory,
        RateLimiterFactory $apiImageLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiImageLimiter);

        $userAvatarDetach($this->getUserOrThrow());

        return new JsonResponse(
            $this->serializeUser($factory->createDto($this->getUserOrThrow())),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'User cover deleted',
        content: new Model(type: UserResponseDto::class),
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
    #[OA\Tag(name: 'user')]
    #[Security(name: 'oauth2', scopes: ['user:profile:edit'])]
    #[IsGranted('ROLE_OAUTH2_USER:PROFILE:EDIT')]
    public function cover(
        UserCoverDetach $userCoverDetach,
        UserFactory $factory,
        RateLimiterFactory $apiImageLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiImageLimiter);

        $userCoverDetach($this->getUserOrThrow());

        return new JsonResponse(
            $this->serializeUser($factory->createDto($this->getUserOrThrow())),
            headers: $headers
        );
    }
}
