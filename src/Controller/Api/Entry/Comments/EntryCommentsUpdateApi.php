<?php

declare(strict_types=1);

namespace App\Controller\Api\Entry\Comments;

use App\Controller\Api\Entry\EntriesBaseApi;
use App\Controller\Traits\PrivateContentTrait;
use App\Entity\EntryComment;
use App\Kbin\EntryComment\DTO\EntryCommentRequestDto;
use App\Kbin\EntryComment\DTO\EntryCommentResponseDto;
use App\Kbin\EntryComment\EntryCommentEdit;
use App\Kbin\EntryComment\Factory\EntryCommentFactory;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntryCommentsUpdateApi extends EntriesBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 200,
        description: 'Comment updated',
        content: new Model(type: EntryCommentResponseDto::class),
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
        description: 'You do not have permission to update this comment',
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
        description: 'The id of the comment to update',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'd',
        in: 'query',
        description: 'Comment tree depth to retrieve (-1 for unlimited depth)',
        schema: new OA\Schema(type: 'integer', default: -1),
    )]
    #[OA\RequestBody(content: new Model(
        type: EntryCommentRequestDto::class,
        groups: [
            'common',
            'comment',
            'no-upload',
        ]
    ))]
    #[OA\Tag(name: 'entry_comment')]
    #[Security(name: 'oauth2', scopes: ['entry_comment:edit'])]
    #[IsGranted('ROLE_OAUTH2_ENTRY_COMMENT:EDIT')]
    #[IsGranted('edit', subject: 'comment')]
    public function __invoke(
        #[MapEntity(id: 'comment_id')]
        EntryComment $comment,
        EntryCommentEdit $entryCommentEdit,
        EntryCommentFactory $factory,
        ValidatorInterface $validator,
        RateLimiterFactory $apiUpdateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiUpdateLimiter);

        if (!$this->isGranted('create_content', $comment->magazine)) {
            throw new AccessDeniedHttpException();
        }
        $dto = $this->deserializeComment($factory->createDto($comment));

        $errors = $validator->validate($dto);
        if (\count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        $comment = $entryCommentEdit($comment, $dto);

        return new JsonResponse(
            $this->serializeCommentTree($comment),
            headers: $headers
        );
    }
}
