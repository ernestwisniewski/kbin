<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\OAuth2\Admin;

use App\Controller\Api\BaseApi;
use App\DTO\ClientAccessStatsResponseDto;
use App\Repository\OAuth2ClientAccessRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RetrieveClientStatsApi extends BaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Accesses by interval retrieved. These are not guaranteed to be continuous.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    'data',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: ClientAccessStatsResponseDto::class))
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
        description: 'You do not have permission to view the OAuth2 client stats',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
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
        description: 'The start date of the window to retrieve views in. If not provided defaults to 1 `resolution` ago',
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
        schema: new OA\Schema(type: 'string', enum: ['all', 'year', 'month', 'day', 'hour', 'second', 'milliseconds']),
    )]
    #[OA\Tag(name: 'admin/oauth2')]
    #[IsGranted('ROLE_ADMIN')]
    #[Security(name: 'oauth2', scopes: ['admin:oauth_clients:read'])]
    #[IsGranted('ROLE_OAUTH2_ADMIN:OAUTH_CLIENTS:READ')]
    /**
     * Retrieve oauth2 client access stats in a particular interval.
     */
    public function __invoke(
        Request $request,
        OAuth2ClientAccessRepository $repository,
        RateLimiterFactory $apiModerateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);
        $resolution = $request->get('resolution');

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
            $stats = $repository->getStats($resolution, $start, $end);
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
}
