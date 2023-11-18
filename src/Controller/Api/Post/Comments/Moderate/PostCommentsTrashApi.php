<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Post\Comments\Moderate;

use App\Controller\Api\Post\PostsBaseApi;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\PostComment;
use App\Kbin\PostComment\DTO\PostCommentResponseDto;
use App\Kbin\PostComment\Factory\PostCommentFactory;
use App\Kbin\PostComment\PostCommentRestore;
use App\Kbin\PostComment\PostCommentTrash;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostCommentsTrashApi extends PostsBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Comment trashed',
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
        response: 403,
        description: 'You are not authorized to trash this comment',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
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
        description: 'The comment to trash',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'moderation/post_comment')]
    #[Security(name: 'oauth2', scopes: ['moderate:post_comment:trash'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:POST_COMMENT:TRASH')]
    #[IsGranted('moderate', subject: 'comment')]
    public function trash(
        #[MapEntity(id: 'comment_id')]
        PostComment $comment,
        PostCommentTrash $postCommentTrash,
        PostCommentFactory $factory,
        RateLimiterFactory $apiModerateLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $moderator = $this->getUserOrThrow();

        $postCommentTrash($moderator, $comment);

        // Force response to have all fields visible
        $visibility = $comment->getVisibility();
        $comment->visibility = VisibilityInterface::VISIBILITY_VISIBLE;
        $response = $this->serializePostComment($factory->createDto($comment))->jsonSerialize();
        $response['visibility'] = $visibility;

        return new JsonResponse(
            $response,
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Comment restored',
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
        response: 400,
        description: 'The comment was not in the trashed state',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You are not authorized to restore this comment',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
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
        description: 'The comment to restore',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'moderation/post_comment')]
    #[Security(name: 'oauth2', scopes: ['moderate:post_comment:trash'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:POST_COMMENT:TRASH')]
    #[IsGranted('moderate', subject: 'comment')]
    public function restore(
        #[MapEntity(id: 'comment_id')]
        PostComment $comment,
        PostCommentRestore $postCommentRestore,
        PostCommentFactory $factory,
        RateLimiterFactory $apiModerateLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $moderator = $this->getUserOrThrow();

        try {
            $postCommentRestore($moderator, $comment);
        } catch (\Exception $e) {
            throw new BadRequestHttpException('The comment cannot be restored because it was not trashed!');
        }

        return new JsonResponse(
            $this->serializePostComment($factory->createDto($comment)),
            headers: $headers
        );
    }
}
