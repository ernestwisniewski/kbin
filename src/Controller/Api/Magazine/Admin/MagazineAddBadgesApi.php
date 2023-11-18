<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\Magazine\Admin;

use App\Controller\Api\Magazine\MagazineBaseApi;
use App\Entity\Magazine;
use App\Kbin\Entry\Badge\EntryBadgeCreate;
use App\Kbin\Entry\DTO\EntryBadgeDto;
use App\Kbin\Magazine\DTO\MagazineResponseDto;
use App\Kbin\Magazine\Factory\MagazineFactory;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MagazineAddBadgesApi extends MagazineBaseApi
{
    #[OA\Response(
        response: 201,
        description: 'Badge created',
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
        description: 'Badge name invalid',
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
    #[OA\RequestBody(content: new Model(type: EntryBadgeDto::class, groups: ['create-badge']))]
    #[OA\Tag(name: 'moderation/magazine/owner')]
    #[Security(name: 'oauth2', scopes: ['moderate:magazine_admin:badges'])]
    #[IsGranted('ROLE_OAUTH2_MODERATE:MAGAZINE_ADMIN:BADGES')]
    #[IsGranted('edit', subject: 'magazine')]
    /**
     * Add a badge to the magazine.
     */
    public function __invoke(
        #[MapEntity(id: 'magazine_id')]
        Magazine $magazine,
        EntryBadgeCreate $entryBadgeCreate,
        MagazineFactory $factory,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        RateLimiterFactory $apiModerateLimiter
    ): JsonResponse {
        $headers = $this->rateLimit($apiModerateLimiter);

        $request = $this->request->getCurrentRequest();
        /**
         * @var EntryBadgeDto $dto
         */
        $dto = $serializer->deserialize($request->getContent(), EntryBadgeDto::class, 'json', ['groups' => ['create-badge']]
        );

        $dto->magazine = $magazine;

        $errors = $validator->validate($dto);
        if (0 < \count($errors)) {
            throw new BadRequestHttpException((string) $errors);
        }

        $entryBadgeCreate($dto);

        return new JsonResponse(
            $this->serializeMagazine($factory->createDto($magazine)),
            headers: $headers
        );
    }
}
