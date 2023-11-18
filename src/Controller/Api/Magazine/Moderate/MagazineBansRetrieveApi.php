<?php

declare(strict_types=1);

namespace App\Controller\Api\Magazine\Moderate;

use App\Controller\Api\Magazine\MagazineBaseApi;
use App\Controller\Traits\PrivateContentTrait;
use App\Entity\Magazine;
use App\Entity\MagazineBan;
use App\Kbin\Magazine\DTO\MagazineBanResponseDto;
use App\Kbin\Magazine\Factory\MagazineFactory;
use App\Repository\MagazineRepository;
use App\Schema\PaginationSchema;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineBansRetrieveApi extends MagazineBaseApi
{
    use PrivateContentTrait;

    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of bans',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: MagazineBanResponseDto::class))
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
        response: 403,
        description: 'You are not allowed to view this magazine\'s ban list',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\ForbiddenErrorSchema::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Page number not valid',
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
        description: 'Magazine to retrieve bans from',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'p',
        description: 'Page of bans to retrieve',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of bans per page',
        in: 'query',
        schema: new OA\Schema(
            type: 'integer',
            default: MagazineRepository::PER_PAGE,
            minimum: self::MIN_PER_PAGE,
            maximum: self::MAX_PER_PAGE
        )
    )]
    #[OA\Tag(name: 'moderation/magazine')]
    #[Security(name: 'oauth2', scopes: ['moderate:magazine:ban:read'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:MAGAZINE:BAN:READ')]
    #[IsGranted('moderate', subject: 'magazine')]
    public function collection(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        MagazineRepository $repository,
        MagazineFactory $factory,
        RateLimiterFactory $apiModerateLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $request = $this->request->getCurrentRequest();
        $bans = $repository->findBans(
            $magazine,
            $this->getPageNb($request),
            self::constrainPerPage($request->get('perPage', MagazineRepository::PER_PAGE))
        );

        $dtos = [];
        foreach ($bans->getCurrentPageResults() as $value) {
            \assert($value instanceof MagazineBan);
            array_push($dtos, $factory->createBanDto($value));
        }

        return new JsonResponse(
            $this->serializePaginated($dtos, $bans),
            headers: $headers
        );
    }
}
