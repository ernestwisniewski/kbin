<?php

declare(strict_types=1);

namespace App\Controller\Api\Magazine\Admin;

use App\Controller\Api\Magazine\MagazineBaseApi;
use App\DTO\MagazineResponseDto;
use App\Entity\Magazine;
use App\Factory\MagazineFactory;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineAddTagsApi extends MagazineBaseApi
{
    #[OA\Response(
        response: 201,
        description: 'Tag created',
        content: new Model(type: MagazineResponseDto::class),
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
        description: 'Tag not present, does not match /^[a-z]{2,32}$/, or already exists on magazine',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'You do not have permission to update this magazine',
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
        description: 'The id of the magazine to update',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'tag',
        in: 'path',
        description: 'The tag to add',
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Tag(name: 'moderation/magazine/owner')]
    #[Security(name: 'oauth2', scopes: ['moderate:magazine_admin:tags'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:MAGAZINE_ADMIN:TAGS')]
    #[IsGranted('edit', subject: 'magazine')]
    /**
     * Add a tag to the magazine.
     */
    public function __invoke(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        string $tag,
        EntityManagerInterface $entityManager,
        MagazineFactory $factory,
        RateLimiterFactory $apiModerateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        if (null === $tag) {
            throw new BadRequestHttpException('Tag is required');
        }

        if (null !== $magazine->tags && false !== array_search($tag, $magazine->tags)) {
            throw new BadRequestHttpException('Tag exists on magazine already');
        }

        if (1 !== preg_match('/^[a-z]{2,32}$/', $tag)) {
            throw new BadRequestHttpException('Invalid tag');
        }

        if (null === $magazine->tags) {
            $magazine->tags = [];
        }

        array_push($magazine->tags, $tag);

        $entityManager->flush();

        return new JsonResponse(
            $this->serializeMagazine($factory->createDto($magazine)),
            headers: $headers
        );
    }
}
