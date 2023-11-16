<?php

declare(strict_types=1);

namespace App\Controller\Api\Entry\Comments;

use App\Controller\Api\Entry\EntriesBaseApi;
use App\Controller\Traits\PrivateContentTrait;
use App\Entity\EntryComment;
use App\Kbin\EntryComment\EntryCommentDelete;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryCommentsDeleteApi extends EntriesBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 204,
        description: 'Comment deleted successfully',
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
        description: 'You are not authorized to delete this comment',
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
        description: 'The comment to delete',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'entry_comment')]
    #[Security(name: 'oauth2', scopes: ['entry_comment:delete'])]
    #[IsGranted('ROLE_OAUTH2_ENTRY_COMMENT:DELETE')]
    #[IsGranted('delete', subject: 'comment')]
    public function __invoke(
        #[MapEntity(id: 'comment_id')]
        EntryComment $comment,
        EntryCommentDelete $entryCommentDelete,
        RateLimiterFactory $apiDeleteLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiDeleteLimiter);

        $entryCommentDelete($this->getUserOrThrow(), $comment);

        return new JsonResponse(status: 204, headers: $headers);
    }
}
