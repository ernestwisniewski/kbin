<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\DTO\ClientConsentsRequestDto;
use App\DTO\ClientConsentsResponseDto;
use App\Entity\OAuth2UserConsent;
use App\Factory\ClientConsentsFactory;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserUpdateOAuthConsentsApi extends UserBaseApi
{
    public const PER_PAGE = 15;

    #[OA\Response(
        response: 200,
        description: 'Updates the consent',
        content: new Model(type: ClientConsentsResponseDto::class),
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
        description: 'The request was invalid',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Permission denied due to missing or expired token',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\UnauthorizedErrorSchema::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'Either you do not have permission to edit this consent, or you attempted to add additional consents not already granted',
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
        name: 'consent_id',
        description: 'Client consent to update',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(content: new Model(type: ClientConsentsRequestDto::class))]
    #[OA\Tag(name: 'oauth')]
    #[Security(name: 'oauth2', scopes: ['user:oauth_clients:edit'])]
    #[IsGranted('ROLE_OAUTH2_USER:OAUTH_CLIENTS:EDIT')]
    #[IsGranted('edit', subject: 'consent')]
    /**
     * This API can be used to remove scopes from an oauth client.
     *
     * The API cannot, however, add extra scopes the user has not consented to. That's what the OAuth flow is for ;)
     * This endpoint will not revoke any tokens that currently exist with the given scopes, those tokens will need to be revoked elsewhere.
     */
    public function __invoke(
        #[MapEntity(id: 'consent_id')]
        OAuth2UserConsent $consent,
        ClientConsentsFactory $factory,
        RateLimiterFactory $apiReadLimiter,
    ): JsonResponse {
        $headers = $this->rateLimit($apiReadLimiter);

        $request = $this->request->getCurrentRequest();
        /** @var ClientConsentsRequestDto $dto */
        $dto = $this->serializer->deserialize($request->getContent(), ClientConsentsRequestDto::class, 'json');

        $errors = $this->validator->validate($dto);
        if (0 < \count($errors)) {
            throw new BadRequestHttpException((string) $errors);
        }

        if (array_intersect($dto->scopes, $consent->getScopes()) !== $dto->scopes) {
            // $dto->scopesGranted is not a subset of the current scopes
            // The client is attempting to request more scopes than it currently has
            throw new AccessDeniedHttpException('An API client cannot add scopes with this API, only remove them.');
        }

        $consent->setScopes($dto->scopes);
        $this->entityManager->flush();

        return new JsonResponse(
            $factory->createDto($consent),
            headers: $headers
        );
    }
}
