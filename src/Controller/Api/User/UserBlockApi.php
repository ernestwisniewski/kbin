<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\DTO\UserResponseDto;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Service\UserManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserBlockApi extends UserBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'User blocked',
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
        response: 400,
        description: 'You cannot block yourself',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found',
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
        name: 'user_id',
        in: 'path',
        description: 'The user to block',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'user')]
    #[Security(name: 'oauth2', scopes: ['user:block'])]
    #[IsGranted('ROLE_OAUTH2_USER:BLOCK')]
    public function block(
        #[MapEntity(id: 'user_id')]
        User $user,
        UserManager $manager,
        UserFactory $factory,
        RateLimiterFactory $apiUpdateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiUpdateLimiter);

        if ($user->getId() === $this->getUserOrThrow()->getId()) {
            throw new BadRequestHttpException('You cannot block yourself');
        }

        $manager->block($this->getUserOrThrow(), $user);

        return new JsonResponse(
            $this->serializeUser($factory->createDto($user)),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'User unblocked',
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
        response: 400,
        description: 'You cannot block yourself',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found',
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
        name: 'user_id',
        in: 'path',
        description: 'The user to unblock',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'user')]
    #[Security(name: 'oauth2', scopes: ['user:block'])]
    #[IsGranted('ROLE_OAUTH2_USER:BLOCK')]
    public function unblock(
        #[MapEntity(id: 'user_id')]
        User $user,
        UserManager $manager,
        UserFactory $factory,
        RateLimiterFactory $apiUpdateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiUpdateLimiter);

        if ($user->getId() === $this->getUserOrThrow()->getId()) {
            throw new BadRequestHttpException('You cannot block yourself');
        }

        $manager->unblock($this->getUserOrThrow(), $user);

        return new JsonResponse(
            $this->serializeUser($factory->createDto($user)),
            headers: $headers
        );
    }
}
