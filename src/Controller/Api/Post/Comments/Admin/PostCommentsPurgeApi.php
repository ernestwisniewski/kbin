<?php

declare(strict_types=1);

namespace App\Controller\Api\Post\Comments\Admin;

use App\Controller\Api\Post\PostsBaseApi;
use App\Entity\PostComment;
use App\Kbin\PostComment\PostCommentPurge;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostCommentsPurgeApi extends PostsBaseApi
{
    #[OA\Response(
        response: 204,
        description: 'Comment purged',
        content: null,
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
        description: 'You are not authorized to purge this comment',
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
        description: 'The comment to purge',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'admin/post_comment')]
    #[IsGranted('ROLE_ADMIN')]
    #[Security(name: 'oauth2', scopes: ['admin:post_comment:purge'])]
    #[IsGranted('ROLE_OAUTH2_ADMIN:POST_COMMENT:PURGE')]
    #[IsGranted('purge', subject: 'comment')]
    /**
     * Purges a post comment from the instance, deleting it completely. This action is irreversible.
     */
    public function __invoke(
        #[MapEntity(id: 'comment_id')]
        PostComment $comment,
        PostCommentPurge $postCommentPurge,
        RateLimiterFactory $apiModerateLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $postCommentPurge($this->getUserOrThrow(), $comment);

        return new JsonResponse(
            status: 204,
            headers: $headers
        );
    }
}
