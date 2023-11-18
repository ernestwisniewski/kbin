<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Entry\Comments;

use App\Controller\Api\Entry\EntriesBaseApi;
use App\Entity\Contracts\VotableInterface;
use App\Entity\EntryComment;
use App\Kbin\EntryComment\DTO\EntryCommentResponseDto;
use App\Kbin\EntryComment\Factory\EntryCommentFactory;
use App\Kbin\Vote\VoteCreate;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryCommentsVoteApi extends EntriesBaseApi
{
    // TODO: Bots should not be able to vote
    //       *sad beep boop*
    #[OA\Response(
        response: 200,
        description: 'Comment vote changed',
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
        response: 400,
        description: 'Vote choice was not valid',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
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
        description: 'The comment to vote upon',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'choice',
        in: 'path',
        description: 'The user\'s voting choice. 0 clears the user\'s vote.',
        schema: new OA\Schema(type: 'integer', enum: VotableInterface::VOTE_CHOICES),
    )]
    #[OA\Tag(name: 'entry_comment')]
    #[Security(name: 'oauth2', scopes: ['entry_comment:vote'])]
    #[IsGranted('ROLE_OAUTH2_ENTRY_COMMENT:VOTE')]
    public function __invoke(
        #[MapEntity(id: 'comment_id')]
        EntryComment $comment,
        int $choice,
        VoteCreate $voteCreate,
        EntryCommentFactory $factory,
        RateLimiterFactory $apiVoteLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiVoteLimiter);

        if (!\in_array($choice, VotableInterface::VOTE_CHOICES)) {
            throw new BadRequestHttpException('Vote must be either -1, 0, or 1');
        }

        // Rate limiting handled above
        $voteCreate($choice, $comment, $this->getUserOrThrow(), rateLimit: false);

        return new JsonResponse(
            $this->serializeComment($factory->createDto($comment)),
            headers: $headers
        );
    }
}
