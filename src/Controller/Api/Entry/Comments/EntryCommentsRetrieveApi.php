<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Entry\Comments;

use App\Controller\Api\Entry\EntriesBaseApi;
use App\Controller\Traits\PrivateContentTrait;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Kbin\EntryComment\DTO\EntryCommentResponseDto;
use App\Kbin\EntryComment\EntryCommentPageView;
use App\Repository\Criteria;
use App\Repository\EntryCommentRepository;
use App\Schema\PaginationSchema;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class EntryCommentsRetrieveApi extends EntriesBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 200,
        description: 'Returns comments from the entry',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: EntryCommentResponseDto::class))
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
        response: 404,
        description: 'Entry not found',
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
        name: 'entry_id',
        in: 'path',
        description: 'The entry to retrieve comments from',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'sortBy',
        in: 'query',
        description: 'The order to retrieve comments by',
        schema: new OA\Schema(
            type: 'string',
            enum: EntryCommentPageView::SORT_OPTIONS,
            default: EntryCommentPageView::SORT_DEFAULT
        )
    )]
    #[OA\Parameter(
        name: 'time',
        in: 'query',
        description: 'The maximum age of retrieved comments',
        schema: new OA\Schema(
            type: 'string',
            default: Criteria::TIME_ALL,
            enum: Criteria::TIME_ROUTES_EN
        )
    )]
    #[OA\Parameter(
        name: 'p',
        in: 'query',
        description: 'The page of comments to retrieve',
        schema: new OA\Schema(type: 'integer', default: 1),
    )]
    #[OA\Parameter(
        name: 'perPage',
        in: 'query',
        description: 'The number of top level comments per page',
        schema: new OA\Schema(
            type: 'integer',
            default: EntryCommentRepository::PER_PAGE,
            minimum: self::MIN_PER_PAGE,
            maximum: self::MAX_PER_PAGE
        ),
    )]
    #[OA\Parameter(
        name: 'd',
        in: 'query',
        description: 'The depth of comment trees retrieved',
        schema: new OA\Schema(
            type: 'integer', default: self::DEPTH, minimum: self::MIN_DEPTH, maximum: self::MAX_DEPTH
        ),
    )]
    #[OA\Parameter(
        name: 'lang[]',
        description: 'Language(s) of comments to return',
        in: 'query',
        explode: true,
        allowReserved: true,
        schema: new OA\Schema(
            type: 'array',
            items: new OA\Items(type: 'string')
        )
    )]
    #[OA\Parameter(
        name: 'usePreferredLangs',
        description: 'Filter by a user\'s preferred languages? (Requires authentication and takes precedence over lang[])',
        in: 'query',
        schema: new OA\Schema(type: 'boolean', default: false),
    )]
    #[OA\Tag(name: 'entry')]
    public function __invoke(
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        EntryCommentRepository $commentsRepository,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);

        $this->handlePrivateContent($entry);

        $request = $this->request->getCurrentRequest();
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($request->get('sortBy', Criteria::SORT_HOT)));
        $criteria->entry = $entry;
        $criteria->perPage = self::constrainPerPage($request->get('perPage', EntryCommentRepository::PER_PAGE));
        $criteria->setTime($criteria->resolveTime($request->get('time', Criteria::TIME_ALL)));

        $this->handleLanguageCriteria($criteria);

        $comments = $commentsRepository->findByCriteria($criteria);

        $commentsRepository->hydrate(...$comments);
        $commentsRepository->hydrateChildren(...$comments);

        $dtos = [];
        foreach ($comments->getCurrentPageResults() as $value) {
            \assert($value instanceof EntryComment);
            array_push($dtos, $this->serializeCommentTree($value));
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $comments),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns the comment',
        content: new Model(type: EntryCommentResponseDto::class)
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
    #[OA\Parameter(
        name: 'comment_id',
        in: 'path',
        description: 'The comment to retrieve',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'd',
        in: 'query',
        description: 'Comment tree depth to retrieve',
        schema: new OA\Schema(
            type: 'integer', default: self::DEPTH, minimum: self::MIN_DEPTH, maximum: self::MAX_DEPTH
        ),
    )]
    #[OA\Tag(name: 'entry_comment')]
    public function single(
        #[MapEntity(id: 'comment_id')]
        EntryComment $comment,
        EntryCommentRepository $commentsRepository,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);
        $this->handlePrivateContent($comment);

        $commentsRepository->hydrate($comment);
        $commentsRepository->hydrateChildren($comment);

        return new JsonResponse(
            $this->serializeCommentTree($comment),
            headers: $headers
        );
    }
}
