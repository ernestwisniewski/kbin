<?php

declare(strict_types=1);

namespace App\Controller\Api\Magazine;

use App\DTO\MagazineThemeResponseDto;
use App\Entity\Magazine;
use App\Factory\MagazineFactory;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class MagazineRetrieveThemeApi extends MagazineBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Theme retrieved',
        content: new Model(type: MagazineThemeResponseDto::class),
        headers: [
            new OA\Header(header: 'X-RateLimit-Remaining', schema: new OA\Schema(type: 'integer'), description: 'Number of requests left until you will be rate limited'),
            new OA\Header(header: 'X-RateLimit-Retry-After', schema: new OA\Schema(type: 'integer'), description: 'Unix timestamp to retry the request after'),
            new OA\Header(header: 'X-RateLimit-Limit', schema: new OA\Schema(type: 'integer'), description: 'Number of requests available'),
        ]
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to expired token',
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
        description: 'The id of the magazine to retrieve the theme from',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Tag(name: 'magazine')]
    /**
     * Retrieve the magazine's theme.
     */
    public function __invoke(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        MagazineFactory $magazineFactory,
        RateLimiterFactory $apiReadLimiter,
        RateLimiterFactory $anonymousApiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter, $anonymousApiReadLimiter);

        $imageDto = $magazine->icon ? $this->imageFactory->createDto($magazine->icon) : null;
        $dto = MagazineThemeResponseDto::create($magazineFactory->createDto($magazine), $magazine->customCss, $imageDto);

        return new JsonResponse(
            $dto,
            headers: $headers
        );
    }
}
