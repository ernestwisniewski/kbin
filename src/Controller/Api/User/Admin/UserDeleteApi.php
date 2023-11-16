<?php

declare(strict_types=1);

namespace App\Controller\Api\User\Admin;

use App\Controller\Api\User\UserBaseApi;
use App\DTO\UserResponseDto;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Service\UserManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserDeleteApi extends UserBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'User deleted',
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
        response: 403,
        description: 'You are not authorized to delete this user',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
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
        description: 'The user to delete',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'admin/user')]
    #[IsGranted('ROLE_ADMIN')]
    #[Security(name: 'oauth2', scopes: ['admin:user:delete'])]
    #[IsGranted('ROLE_OAUTH2_ADMIN:USER:DELETE')]
    /**
     * Deletes the user from the instance while leaving a record of the username that was deleted.
     * This action is irreversable.
     */
    public function __invoke(
        #[MapEntity(id: 'user_id')]
        User $user,
        UserManager $manager,
        UserFactory $factory,
        RateLimiterFactory $apiModerateLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $manager->delete($user);

        return new JsonResponse(
            $this->serializeUser($factory->createDto($user)),
            headers: $headers
        );
    }
}
