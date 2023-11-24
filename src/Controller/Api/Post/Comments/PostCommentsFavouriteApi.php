<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Post\Comments;

use App\Controller\Api\Post\PostsBaseApi;
use App\Entity\PostComment;
use App\Kbin\Favourite\FavouriteToggle;
use App\Kbin\PostComment\DTO\PostCommentResponseDto;
use App\Kbin\PostComment\Factory\PostCommentFactory;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostCommentsFavouriteApi extends PostsBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Comment favourite status toggled',
        content: new Model(type: PostCommentResponseDto::class),
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
        description: 'Comment not found',
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
        name: 'comment_id',
        in: 'path',
        description: 'The comment to favourite',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'd',
        in: 'query',
        description: 'Comment tree depth to retrieve (-1 for unlimited depth)',
        schema: new OA\Schema(type: 'integer', default: -1),
    )]
    #[OA\Tag(name: 'post_comment')]
    // TODO: Bots should not be able to vote
    //       *sad beep boop*
    #[Security(name: 'oauth2', scopes: ['post_comment:vote'])]
    #[IsGranted('ROLE_OAUTH2_POST_COMMENT:VOTE')]
    public function __invoke(
        #[MapEntity(id: 'comment_id')]
        PostComment $comment,
        FavouriteToggle $favouriteToggle,
        PostCommentFactory $factory,
        RateLimiterFactory $apiVoteLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiVoteLimiter);

        $favouriteToggle($this->getUserOrThrow(), $comment);

        return new JsonResponse(
            $this->serializePostComment($factory->createDto($comment)),
            headers: $headers
        );
    }
}
