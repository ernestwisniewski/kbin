<?php

declare(strict_types=1);

namespace App\Controller\Api\User\Admin;

use App\Controller\Api\User\UserBaseApi;
use App\Entity\User;
use App\Kbin\User\DTO\UserBanResponseDto;
use App\Kbin\User\Factory\UserFactory;
use App\Repository\UserRepository;
use App\Schema\PaginationSchema;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserRetrieveBannedApi extends UserBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of banned users',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: UserBanResponseDto::class))
                ),
                new OA\Property(
                    property: 'pagination',
                    ref: new Model(type: PaginationSchema::class)
                ),
            ]
        ),
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
        description: 'You are not permitted to view the list of banned users',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
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
        name: 'p',
        description: 'Page of users to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of users per page',
        in: 'query',
        schema: new OA\Schema(
            type: 'integer',
            default: UserRepository::PER_PAGE,
            minimum: self::MIN_PER_PAGE,
            maximum: self::MAX_PER_PAGE
        )
    )]
    #[OA\Parameter(
        name: 'group',
        description: 'What group of users to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'string', default: UserRepository::USERS_ALL, enum: UserRepository::USERS_OPTIONS)
    )]
    #[OA\Tag(name: 'admin/user')]
    #[IsGranted('ROLE_ADMIN')]
    #[Security(name: 'oauth2', scopes: ['admin:user:ban'])]
    #[IsGranted('ROLE_OAUTH2_ADMIN:USER:BAN')]
    /** Retrieves a list of users currently banned from the instance */
    public function collection(
        UserRepository $userRepository,
        UserFactory $factory,
        RateLimiterFactory $apiModerateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $request = $this->request->getCurrentRequest();
        $group = $request->get('group', UserRepository::USERS_ALL);

        $users = $userRepository->findBannedPaginated(
            $this->getPageNb($request),
            $group,
            $this->constrainPerPage($request->get('perPage', UserRepository::PER_PAGE))
        );

        $dtos = [];
        foreach ($users->getCurrentPageResults() as $value) {
            \assert($value instanceof User);
            array_push($dtos, new UserBanResponseDto($factory->createDto($value), $value->isBanned));
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $users),
            headers: $headers
        );
    }
}
