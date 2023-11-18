<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Magazine\Admin;

use App\Controller\Api\Magazine\MagazineBaseApi;
use App\Entity\Magazine;
use App\Kbin\Magazine\DTO\MagazineResponseDto;
use App\Kbin\Magazine\Factory\MagazineFactory;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MagazineRemoveTagsApi extends MagazineBaseApi
{
    #[OA\Response(
        response: 200,
        description: 'Tag deleted',
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
        description: 'Magazine or tag not found',
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
        description: 'The tag to remove',
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Tag(name: 'moderation/magazine/owner')]
    #[Security(name: 'oauth2', scopes: ['moderate:magazine_admin:tags'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:MAGAZINE_ADMIN:TAGS')]
    #[IsGranted('edit', subject: 'magazine')]
    /**
     * Remove a tag from the magazine.
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

        if (!$magazine->userIsOwner($this->getUserOrThrow())) {
            throw new AccessDeniedHttpException();
        }

        if (null === $tag) {
            throw new BadRequestHttpException('Tag must be present');
        }

        if (null === $magazine->tags) {
            $index = false;
        } else {
            $index = array_search($tag, $magazine->tags);
        }

        if (false === $index) {
            throw new BadRequestHttpException('Tag is not present on magazine');
        }

        array_splice($magazine->tags, $index, 1);

        if (0 === \count($magazine->tags)) {
            $magazine->tags = null;
        }

        $entityManager->flush();

        return new JsonResponse(
            $this->serializeMagazine($factory->createDto($magazine)),
            headers: $headers
        );
    }
}
