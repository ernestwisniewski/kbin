<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Search;

use App\Controller\Api\BaseApi;
use App\Controller\Traits\PrivateContentTrait;
use App\Entity\Contracts\ContentInterface;
use App\Kbin\Magazine\Factory\MagazineFactory;
use App\Kbin\User\Factory\UserFactory;
use App\Repository\SearchRepository;
use App\Schema\ContentSchema;
use App\Schema\PaginationSchema;
use App\Schema\SearchActorSchema;
use App\Service\SearchManager;
use App\Service\SettingsManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class SearchRetrieveApi extends BaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of content, along with any ActivityPub actors that matched the query by username, or ActivityPub objects that matched the query by URL. Actors and objects are not paginated',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: ContentSchema::class)
                    )
                ),
                new OA\Property(
                    property: 'pagination',
                    ref: new Model(type: PaginationSchema::class)
                ),
                new OA\Property(
                    property: 'apActors',
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: SearchActorSchema::class)
                    )
                ),
                new OA\Property(
                    property: 'apObjects',
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: ContentSchema::class)
                    )
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
        description: 'The search query parameter `q` is required!',
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
        name: 'p',
        description: 'Page of items to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of items per page',
        in: 'query',
        schema: new OA\Schema(
            type: 'integer',
            default: SearchRepository::PER_PAGE,
            minimum: self::MIN_PER_PAGE,
            maximum: self::MAX_PER_PAGE
        )
    )]
    #[OA\Parameter(
        name: 'q',
        description: 'Search term',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'search')]
    public function __invoke(
        SearchManager $manager,
        UserFactory $userFactory,
        MagazineFactory $magazineFactory,
        SettingsManager $settingsManager,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);

        $request = $this->request->getCurrentRequest();
        $q = $request->get('q');
        if (null === $q) {
            throw new BadRequestHttpException();
        }

        $page = $this->getPageNb($request);
        $perPage = self::constrainPerPage($request->get('perPage', SearchRepository::PER_PAGE));

        $items = $manager->findPaginated($q, $page, $perPage);
        $dtos = [];
        foreach ($items->getCurrentPageResults() as $value) {
            \assert($value instanceof ContentInterface);
            array_push($dtos, $this->serializeContentInterface($value));
        }

        $response = $this->serializePaginated($dtos, $items);

        $response['apActors'] = [];
        $response['apObjects'] = [];
        $actors = [];
        $objects = [];
        if (!$settingsManager->get('KBIN_FEDERATED_SEARCH_ONLY_LOGGEDIN') || $this->getUser()) {
            $actors = $manager->findActivityPubActorsByUsername($q);
            $objects = $manager->findActivityPubObjectsByURL($q);
        }

        foreach ($actors as $actor) {
            switch ($actor['type']) {
                case 'user':
                    $response['apActors'][] = [
                        'type' => 'user',
                        'object' => $this->serializeUser($userFactory->createDto($actor['object'])),
                    ];
                    break;
                case 'magazine':
                    $response['apActors'][] = [
                        'type' => 'magazine',
                        'object' => $this->serializeMagazine($magazineFactory->createDto($actor['object'])),
                    ];
                    break;
            }
        }

        foreach ($objects as $object) {
            \assert($object instanceof ContentInterface);
            $response['apObjects'][] = $this->serializeContentInterface($object);
        }

        return new JsonResponse(
            $response,
            headers: $headers
        );
    }
}
