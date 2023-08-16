<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\DTO\ClientConsentsResponseDto;
use App\Entity\OAuth2UserConsent;
use App\Factory\ClientConsentsFactory;
use App\Schema\PaginationSchema;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Pagerfanta\Doctrine\Collections\CollectionAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserRetrieveOAuthConsentsApi extends UserBaseApi
{
    public const PER_PAGE = 15;

    #[OA\Response(
        response: 200,
        description: 'Returns the specific OAuth2 consent',
        content: new Model(type: ClientConsentsResponseDto::class),
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
        description: 'You do not have permission to view this consent',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Consent not found',
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
        name: 'consent_id',
        description: 'Client consent to retrieve',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'oauth')]
    #[Security(name: 'oauth2', scopes: ['user:oauth_clients:read'])]
    #[IsGranted('ROLE_OAUTH2_USER:OAUTH_CLIENTS:READ')]
    #[IsGranted('view', subject: 'consent')]
    public function __invoke(
        #[MapEntity(id: 'consent_id')]
        OAuth2UserConsent $consent,
        ClientConsentsFactory $factory,
        RateLimiterFactory $apiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter);

        return new JsonResponse(
            $factory->createDto($consent)->jsonSerialize(),
            headers: $headers
        );
    }

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of OAuth2 consents given to clients by the user',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: ClientConsentsResponseDto::class))
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
        description: 'You are not authorized to view this page',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Page not found',
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
        name: 'p',
        description: 'Page of clients to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of clients to retrieve per page',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: self::PER_PAGE, minimum: self::MIN_PER_PAGE, maximum: self::MAX_PER_PAGE)
    )]
    #[OA\Tag(name: 'oauth')]
    #[Security(name: 'oauth2', scopes: ['user:oauth_clients:read'])]
    #[IsGranted('ROLE_OAUTH2_USER:OAUTH_CLIENTS:READ')]
    public function collection(
        ClientConsentsFactory $factory,
        RateLimiterFactory $apiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter);

        $pagerfanta = new Pagerfanta(
            new CollectionAdapter(
                $this->getUserOrThrow()->getOAuth2UserConsents()
            )
        );

        $request = $this->request->getCurrentRequest();
        $page = $this->getPageNb($request);
        $perPage = self::constrainPerPage($request->get('perPage', self::PER_PAGE));

        try {
            $pagerfanta->setMaxPerPage($perPage);
            $pagerfanta->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $dtos = [];
        foreach ($pagerfanta->getCurrentPageResults() as $consent) {
            assert($consent instanceof OAuth2UserConsent);
            array_push($dtos, $factory->createDto($consent)->jsonSerialize());
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $pagerfanta),
            headers: $headers
        );
    }
}
