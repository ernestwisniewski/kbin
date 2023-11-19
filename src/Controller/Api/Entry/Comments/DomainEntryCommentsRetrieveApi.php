<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Entry\Comments;

use App\Controller\Api\Entry\EntriesBaseApi;
use App\Controller\Traits\PrivateContentTrait;
use App\Entity\Domain;
use App\Kbin\EntryComment\DTO\EntryCommentResponseDto;
use App\Kbin\EntryComment\EntryCommentPageView;
use App\Repository\Criteria;
use App\Repository\EntryCommentRepository;
use App\Repository\EntryRepository;
use App\Schema\PaginationSchema;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class DomainEntryCommentsRetrieveApi extends EntriesBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of comments from a specific domain',
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
        description: 'Domain not found',
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
        name: 'domain_id',
        in: 'path',
        description: 'The domain to retrieve comments from',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'sort',
        in: 'query',
        description: 'The sorting method to use during comment fetch',
        schema: new OA\Schema(
            type: 'string',
            default: EntryCommentPageView::SORT_DEFAULT,
            enum: EntryCommentPageView::SORT_OPTIONS
        )
    )]
    #[OA\Parameter(
        name: 'time',
        in: 'query',
        description: 'The maximum age of retrieved entries',
        schema: new OA\Schema(
            type: 'string',
            default: Criteria::TIME_ALL,
            enum: Criteria::TIME_ROUTES_EN
        )
    )]
    #[OA\Parameter(
        name: 'p',
        description: 'Page of comments to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of comments to retrieve per page',
        in: 'query',
        schema: new OA\Schema(
            type: 'integer',
            default: EntryRepository::PER_PAGE,
            minimum: self::MIN_PER_PAGE,
            maximum: self::MAX_PER_PAGE
        )
    )]
    #[OA\Parameter(
        name: 'd',
        description: 'Depth of comment children to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: self::DEPTH, minimum: self::MIN_DEPTH, maximum: self::MAX_DEPTH)
    )]
    #[OA\Parameter(
        name: 'lang[]',
        description: 'Language(s) of entries to return',
        in: 'query',
        explode: true,
        allowReserved: true,
        schema: new OA\Schema(
            type: 'array',
            items: new OA\Items(type: 'string', default: null, minLength: 2, maxLength: 3)
        )
    )]
    #[OA\Parameter(
        name: 'usePreferredLangs',
        description: 'Filter by a user\'s preferred languages? (Requires authentication and takes precedence over lang[])',
        in: 'query',
        schema: new OA\Schema(type: 'boolean', default: false),
    )]
    #[OA\Tag(name: 'domain')]
    public function __invoke(
        #[MapEntity(id: 'domain_id')]
        Domain $domain,
        EntryCommentRepository $repository,
        RequestStack $request,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);

        $criteria = new EntryCommentPageView((int) $request->getCurrentRequest()->get('p', 1));
        $criteria->sortOption = $request->getCurrentRequest()->get('sort', Criteria::SORT_HOT);
        $criteria->time = $criteria->resolveTime(
            $request->getCurrentRequest()->get('time', Criteria::TIME_ALL)
        );
        $this->handleLanguageCriteria($criteria);

        $criteria->perPage = self::constrainPerPage(
            $request->getCurrentRequest()->get('perPage', EntryRepository::PER_PAGE)
        );

        $criteria->domain = $domain->name;

        $comments = $repository->findByCriteria($criteria);

        $dtos = [];
        foreach ($comments->getCurrentPageResults() as $value) {
            try {
                $this->handlePrivateContent($value);
                array_push($dtos, $this->serializeCommentTree($value));
            } catch (\Exception $e) {
                continue;
            }
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $comments),
            headers: $headers
        );
    }
}
