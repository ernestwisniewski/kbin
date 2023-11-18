<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Post;

use App\Entity\Post;
use App\Kbin\Post\DTO\PostResponseDto;
use App\Kbin\Post\Factory\PostFactory;
use App\Service\FavouriteManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostsFavouriteApi extends PostsBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Post favourite status toggled',
        content: new Model(type: PostResponseDto::class),
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
        description: 'Post not found',
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
        name: 'post_id',
        in: 'path',
        description: 'The post to favourite',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'post')]
    #[Security(name: 'oauth2', scopes: ['post:vote'])]
    #[IsGranted('ROLE_OAUTH2_POST:VOTE')]
    public function __invoke(
        #[MapEntity(id: 'post_id')]
        Post $post,
        FavouriteManager $manager,
        PostFactory $factory,
        RateLimiterFactory $apiVoteLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiVoteLimiter);

        $manager->toggle($this->getUserOrThrow(), $post);

        return new JsonResponse(
            $this->serializePost($factory->createDto($post)),
            headers: $headers
        );
    }
}
