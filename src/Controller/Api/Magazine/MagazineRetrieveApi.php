<?php

declare(strict_types=1);

namespace App\Controller\Api\Magazine;

use App\Controller\Traits\PrivateContentTrait;
use App\DTO\MagazineResponseDto;
use App\Entity\Magazine;
use App\Entity\MagazineBlock;
use App\Entity\MagazineSubscription;
use App\Entity\User;
use App\Factory\MagazineFactory;
use App\PageView\MagazinePageView;
use App\Repository\Criteria;
use App\Repository\MagazineRepository;
use App\Schema\PaginationSchema;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineRetrieveApi extends MagazineBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 200,
        description: 'Returns the Magazine',
        content: new Model(type: MagazineResponseDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
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
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'magazine_id',
        in: 'path',
        description: 'The magazine to retrieve',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'magazine')]
    public function __invoke(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        MagazineFactory $factory,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);

        $dto = $factory->createDto($magazine);

        return new JsonResponse(
            $this->serializeMagazine($dto),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns the magazine for the given name',
        content: new Model(type: MagazineResponseDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
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
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'magazine_name',
        in: 'path',
        description: 'The magazine to retrieve',
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Tag(name: 'magazine')]
    public function byName(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        MagazineFactory $factory,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);

        $dto = $factory->createDto($magazine);

        return new JsonResponse(
            $this->serializeMagazine($dto),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of magazines',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: MagazineResponseDto::class))
                ),
                new OA\Property(
                    property: 'pagination',
                    ref: new Model(type: PaginationSchema::class)
                ),
            ]
        ),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
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
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'p',
        description: 'Page of magazines to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of magazines per page',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: MagazineRepository::PER_PAGE, minimum: self::MIN_PER_PAGE, maximum: self::MAX_PER_PAGE)
    )]
    #[OA\Parameter(
        name: 'q',
        description: 'Magazine search term',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'sort',
        description: 'Sort method to use when retrieving magazines',
        in: 'query',
        schema: new OA\Schema(type: 'string', default: MagazinePageView::SORT_HOT, enum: MagazineRepository::SORT_OPTIONS)
    )]
    #[OA\Parameter(
        name: 'federation',
        description: 'What type of federated magazines to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'string', default: Criteria::AP_ALL, enum: Criteria::AP_OPTIONS)
    )]
    #[OA\Parameter(
        name: 'hide_adult',
        description: 'Options for retrieving adult magazines',
        in: 'query',
        schema: new OA\Schema(type: 'string', default: MagazinePageView::ADULT_HIDE, enum: MagazinePageView::ADULT_OPTIONS)
    )]
    #[OA\Tag(name: 'magazine')]
    public function collection(
        MagazineRepository $repository,
        MagazineFactory $factory,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);

        $request = $this->request->getCurrentRequest();
        $criteria = new MagazinePageView(
            $this->getPageNb($request),
            $request->get('sort', MagazinePageView::SORT_HOT),
            $request->get('federation', Criteria::AP_ALL),
            $request->get('hide_adult', MagazinePageView::ADULT_HIDE),
        );
        $criteria->perPage = self::constrainPerPage($request->get('perPage', MagazineRepository::PER_PAGE));

        if ($q = $request->get('q')) {
            $criteria->query = $q;
        }

        $magazines = $repository->findPaginated($criteria);
        $dtos = [];
        foreach ($magazines->getCurrentPageResults() as $value) {
            assert($value instanceof Magazine);
            array_push($dtos, $this->serializeMagazine($factory->createDto($value)));
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $magazines),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of subscribed magazines',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: MagazineResponseDto::class))
                ),
                new OA\Property(
                    property: 'pagination',
                    ref: new Model(type: PaginationSchema::class)
                ),
            ]
        ),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
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
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'p',
        description: 'Page of magazines to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of magazines per page',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: MagazineRepository::PER_PAGE, minimum: self::MIN_PER_PAGE, maximum: self::MAX_PER_PAGE)
    )]
    #[OA\Tag(name: 'magazine')]
    #[Security(name: 'oauth2', scopes: ['magazine:subscribe'])]
    #[IsGranted('ROLE_OAUTH2_MAGAZINE:SUBSCRIBE')]
    public function subscribed(
        MagazineRepository $repository,
        MagazineFactory $factory,
        RateLimiterFactory $apiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter);

        $request = $this->request->getCurrentRequest();
        $magazines = $repository->findSubscribedMagazines(
            $this->getPageNb($request),
            $this->getUserOrThrow(),
            self::constrainPerPage($request->get('perPage', MagazineRepository::PER_PAGE))
        );

        $dtos = [];
        foreach ($magazines->getCurrentPageResults() as $value) {
            assert($value instanceof MagazineSubscription);
            array_push($dtos, $this->serializeMagazine($factory->createDto($value->magazine)));
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $magazines),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of user\'s subscribed magazines',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: MagazineResponseDto::class))
                ),
                new OA\Property(
                    property: 'pagination',
                    ref: new Model(type: PaginationSchema::class)
                ),
            ]
        ),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'This user does not allow others to view their subscribed magazines',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 429,
        description: 'You are being rate limited',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\TooManyRequestsErrorSchema::class)),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'user_id',
        description: 'User from which to retrieve subscribed magazines',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'p',
        description: 'Page of magazines to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of magazines per page',
        in: 'query',
        schema: new OA\Schema(
            type: 'integer',
            default: MagazineRepository::PER_PAGE,
            minimum: self::MIN_PER_PAGE,
            maximum: self::MAX_PER_PAGE
        )
    )]
    #[OA\Tag(name: 'user')]
    #[Security(name: 'oauth2', scopes: ['magazine:subscribe'])]
    #[IsGranted('ROLE_OAUTH2_MAGAZINE:SUBSCRIBE')]
    public function subscriptions(
        #[MapEntity(id: 'user_id')]
        User $user,
        MagazineRepository $repository,
        MagazineFactory $factory,
        RateLimiterFactory $apiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter);

        if ($user->getId() !== $this->getUserOrThrow()->getId() && !$user->getShowProfileSubscriptions()) {
            throw new AccessDeniedHttpException('You are not permitted to view the magazines this user subscribes to');
        }

        $request = $this->request->getCurrentRequest();
        $magazines = $repository->findSubscribedMagazines(
            $this->getPageNb($request),
            $user,
            self::constrainPerPage($request->get('perPage', MagazineRepository::PER_PAGE))
        );

        $dtos = [];
        foreach ($magazines->getCurrentPageResults() as $value) {
            assert($value instanceof MagazineSubscription);
            array_push($dtos, $this->serializeMagazine($factory->createDto($value->magazine)));
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $magazines),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of moderated magazines',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: MagazineResponseDto::class))
                ),
                new OA\Property(
                    property: 'pagination',
                    ref: new Model(type: PaginationSchema::class)
                ),
            ]
        ),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
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
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'p',
        description: 'Page of magazines to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of magazines per page',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: MagazineRepository::PER_PAGE, minimum: self::MIN_PER_PAGE, maximum: self::MAX_PER_PAGE)
    )]
    #[OA\Tag(name: 'magazine')]
    #[Security(name: 'oauth2', scopes: ['moderate:magazine:list'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:MAGAZINE:LIST')]
    public function moderated(
        MagazineRepository $repository,
        MagazineFactory $factory,
        RateLimiterFactory $apiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter);

        $request = $this->request->getCurrentRequest();
        $magazines = $repository->findModeratedMagazines(
            $this->getUserOrThrow(),
            $this->getPageNb($request),
            self::constrainPerPage($request->get('perPage', MagazineRepository::PER_PAGE))
        );

        $dtos = [];
        foreach ($magazines->getCurrentPageResults() as $value) {
            assert($value instanceof Magazine);
            array_push($dtos, $this->serializeMagazine($factory->createDto($value)));
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $magazines),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of blocked magazines',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: MagazineResponseDto::class))
                ),
                new OA\Property(
                    property: 'pagination',
                    ref: new Model(type: PaginationSchema::class)
                ),
            ]
        ),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
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
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Parameter(
        name: 'p',
        description: 'Page of magazines to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of magazines per page',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: MagazineRepository::PER_PAGE, minimum: self::MIN_PER_PAGE, maximum: self::MAX_PER_PAGE)
    )]
    #[OA\Tag(name: 'magazine')]
    #[Security(name: 'oauth2', scopes: ['magazine:block'])]
    #[IsGranted('ROLE_OAUTH2_MAGAZINE:BLOCK')]
    public function blocked(
        MagazineRepository $repository,
        MagazineFactory $factory,
        RateLimiterFactory $apiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter);

        $request = $this->request->getCurrentRequest();
        $magazines = $repository->findBlockedMagazines(
            $this->getPageNb($request),
            $this->getUserOrThrow(),
            self::constrainPerPage($request->get('perPage', MagazineRepository::PER_PAGE))
        );

        $dtos = [];
        foreach ($magazines->getCurrentPageResults() as $value) {
            assert($value instanceof MagazineBlock);
            array_push($dtos, $this->serializeMagazine($factory->createDto($value->magazine)));
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $magazines),
            headers: $headers
        );
    }
}
