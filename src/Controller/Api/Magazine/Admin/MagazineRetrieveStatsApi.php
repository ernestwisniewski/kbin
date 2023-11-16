<?php

declare(strict_types=1);

namespace App\Controller\Api\Magazine\Admin;

use App\Controller\Api\Magazine\MagazineBaseApi;
use App\DTO\ContentStatsResponseDto;
use App\DTO\ViewStatsResponseDto;
use App\DTO\VoteStatsResponseDto;
use App\Entity\Magazine;
use App\Repository\StatsContentRepository;
use App\Repository\StatsViewsRepository;
use App\Repository\StatsVotesRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineRetrieveStatsApi extends MagazineBaseApi
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
        response: 403,
        description: 'You do not have permission to view the stats of this magazine',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Magazine not found',
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
        name: 'magazine_id',
        in: 'path',
        description: 'The id of the magazine to retrieve stats from',
        schema: new OA\Schema(type: 'integer'),
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
    #[OA\Tag(name: 'moderation/magazine/owner')]
    #[Security(name: 'oauth2', scopes: ['moderate:magazine_admin:stats'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:MAGAZINE_ADMIN:STATS')]
    #[IsGranted('edit', subject: 'magazine')]
    /**
     * Retrieve the views of a magazine over time.
     */
    public function views(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        StatsViewsRepository $repository,
        RateLimiterFactory $apiModerateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);
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
            $stats = $repository->getStats($magazine, $resolution, $start, $end, $local);
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
        response: 403,
        description: 'You do not have permission to view the stats of this magazine',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Magazine not found',
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
        name: 'magazine_id',
        in: 'path',
        description: 'The id of the magazine to retrieve stats from',
        schema: new OA\Schema(type: 'integer'),
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
    #[OA\Tag(name: 'moderation/magazine/owner')]
    #[Security(name: 'oauth2', scopes: ['moderate:magazine_admin:stats'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:MAGAZINE_ADMIN:STATS')]
    #[IsGranted('edit', subject: 'magazine')]
    /**
     * Retrieve the votes of a magazine over time.
     */
    public function votes(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        StatsVotesRepository $repository,
        RateLimiterFactory $apiModerateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);
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
            $stats = $repository->getStats($magazine, $resolution, $start, $end, $local);
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
        response: 403,
        description: 'You do not have permission to view the stats of this magazine',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Magazine not found',
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
        name: 'magazine_id',
        in: 'path',
        description: 'The id of the magazine to retrieve stats from',
        schema: new OA\Schema(type: 'integer'),
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
    #[OA\Tag(name: 'moderation/magazine/owner')]
    #[Security(name: 'oauth2', scopes: ['moderate:magazine_admin:stats'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:MAGAZINE_ADMIN:STATS')]
    #[IsGranted('edit', subject: 'magazine')]
    /**
     * Retrieve the content stats of a magazine over time.
     */
    public function content(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        StatsContentRepository $repository,
        RateLimiterFactory $apiModerateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);
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
            $stats = $repository->getStats($magazine, $resolution, $start, $end, $local);
        } catch (\LogicException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse(
            $stats,
            headers: $headers
        );
    }
}
