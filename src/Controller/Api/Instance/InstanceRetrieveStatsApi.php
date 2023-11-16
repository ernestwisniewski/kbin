<?php

declare(strict_types=1);

namespace App\Controller\Api\Instance;

use App\DTO\ContentStatsResponseDto;
use App\DTO\ViewStatsResponseDto;
use App\DTO\VoteStatsResponseDto;
use App\Repository\StatsContentRepository;
use App\Repository\StatsViewsRepository;
use App\Repository\StatsVotesRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class InstanceRetrieveStatsApi extends InstanceBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Views by interval retrieved. These are not guaranteed to be continuous.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    'data',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: ViewStatsResponseDto::class))
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
        response: 400,
        description: 'Invalid parameters',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
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
        name: 'start',
        in: 'query',
        description: 'The start date of the window to retrieve views in. If not provided defaults to yesterday',
        schema: new OA\Schema(type: 'string', format: 'date'),
    )]
    #[OA\Parameter(
        name: 'end',
        in: 'query',
        description: 'The end date of the window to retrieve views in. If not provided defaults to today',
        schema: new OA\Schema(type: 'string', format: 'date'),
    )]
    #[OA\Parameter(
        name: 'resolution',
        required: true,
        in: 'query',
        description: 'The size of chunks to aggregate views in',
        schema: new OA\Schema(type: 'string', enum: ['all', 'year', 'month', 'day', 'hour']),
    )]
    #[OA\Parameter(
        name: 'local',
        in: 'query',
        description: 'Exclude federated views?',
        schema: new OA\Schema(type: 'boolean', default: false),
    )]
    #[OA\Tag(name: 'instance/stats')]
    /**
     * Retrieve the views of the instance over time.
     */
    public function views(
        StatsViewsRepository $repository,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);
        $request = $this->request->getCurrentRequest();
        $resolution = $request->get('resolution');
        $local = filter_var($request->get('local', false), FILTER_VALIDATE_BOOL);

        try {
            $startString = $request->get('start');
            if (null === $startString) {
                $start = null;
            } else {
                $start = new \DateTime($startString);
            }

            $endString = $request->get('end');
            if (null === $endString) {
                $end = null;
            } else {
                $end = new \DateTime($endString);
            }
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Failed to parse start or end time');
        }

        if (null === $resolution) {
            throw new BadRequestHttpException('Resolution must be provided!');
        }

        try {
            $stats = $repository->getStats(null, $resolution, $start, $end, $local);
        } catch (\LogicException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse(
            [
                'data' => $stats,
            ],
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Votes by interval retrieved. These are not guaranteed to be continuous.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    'entry',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: VoteStatsResponseDto::class))
                ),
                new OA\Property(
                    'entry_comment',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: VoteStatsResponseDto::class))
                ),
                new OA\Property(
                    'post',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: VoteStatsResponseDto::class))
                ),
                new OA\Property(
                    'post_comment',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: VoteStatsResponseDto::class))
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
        response: 400,
        description: 'Invalid parameters',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
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
        name: 'start',
        in: 'query',
        description: 'The start date of the window to retrieve votes in. If not provided defaults to 1 (resolution) ago',
        schema: new OA\Schema(type: 'string', format: 'date'),
    )]
    #[OA\Parameter(
        name: 'end',
        in: 'query',
        description: 'The end date of the window to retrieve votes in. If not provided defaults to today',
        schema: new OA\Schema(type: 'string', format: 'date'),
    )]
    #[OA\Parameter(
        name: 'resolution',
        required: true,
        in: 'query',
        description: 'The size of chunks to aggregate votes in',
        schema: new OA\Schema(type: 'string', enum: ['all', 'year', 'month', 'day', 'hour']),
    )]
    #[OA\Parameter(
        name: 'local',
        in: 'query',
        description: 'Exclude federated votes?',
        schema: new OA\Schema(type: 'boolean', default: false),
    )]
    #[OA\Tag(name: 'instance/stats')]
    /**
     * Retrieve the votes of the instance over time.
     */
    public function votes(
        StatsVotesRepository $repository,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);
        $request = $this->request->getCurrentRequest();
        $resolution = $request->get('resolution');
        $local = filter_var($request->get('local', false), FILTER_VALIDATE_BOOL);

        try {
            $startString = $request->get('start');
            if (null === $startString) {
                $start = null;
            } else {
                $start = new \DateTime($startString);
            }

            $endString = $request->get('end');
            if (null === $endString) {
                $end = null;
            } else {
                $end = new \DateTime($endString);
            }
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Failed to parse start or end time');
        }

        if (null === $resolution) {
            throw new BadRequestHttpException('Resolution must be provided!');
        }

        try {
            $stats = $repository->getStats(null, $resolution, $start, $end, $local);
        } catch (\LogicException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse(
            $stats,
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Submissions by interval retrieved. These are not guaranteed to be continuous.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    'entry',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: ContentStatsResponseDto::class))
                ),
                new OA\Property(
                    'entry_comment',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: ContentStatsResponseDto::class))
                ),
                new OA\Property(
                    'post',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: ContentStatsResponseDto::class))
                ),
                new OA\Property(
                    'post_comment',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: ContentStatsResponseDto::class))
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
        response: 400,
        description: 'Invalid parameters',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
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
        name: 'start',
        in: 'query',
        description: 'The start date of the window to retrieve submissions in. If not provided defaults to 1 (resolution) ago',
        schema: new OA\Schema(type: 'string', format: 'date'),
    )]
    #[OA\Parameter(
        name: 'end',
        in: 'query',
        description: 'The end date of the window to retrieve submissions in. If not provided defaults to today',
        schema: new OA\Schema(type: 'string', format: 'date'),
    )]
    #[OA\Parameter(
        name: 'resolution',
        required: true,
        in: 'query',
        description: 'The size of chunks to aggregate content submissions in',
        schema: new OA\Schema(type: 'string', enum: ['all', 'year', 'month', 'day', 'hour']),
    )]
    #[OA\Parameter(
        name: 'local',
        in: 'query',
        description: 'Exclude federated content?',
        schema: new OA\Schema(type: 'boolean', default: false),
    )]
    #[OA\Tag(name: 'instance/stats')]
    /**
     * Retrieve the content stats of the instance over time.
     */
    public function content(
        StatsContentRepository $repository,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);
        $request = $this->request->getCurrentRequest();
        $resolution = $request->get('resolution');
        $local = filter_var($request->get('local', false), FILTER_VALIDATE_BOOL);

        try {
            $startString = $request->get('start');
            if (null === $startString) {
                $start = null;
            } else {
                $start = new \DateTime($startString);
            }

            $endString = $request->get('end');
            if (null === $endString) {
                $end = null;
            } else {
                $end = new \DateTime($endString);
            }
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Failed to parse start or end time');
        }

        if (null === $resolution) {
            throw new BadRequestHttpException('Resolution must be provided!');
        }

        try {
            $stats = $repository->getStats(null, $resolution, $start, $end, $local);
        } catch (\LogicException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse(
            $stats,
            headers: $headers
        );
    }
}
