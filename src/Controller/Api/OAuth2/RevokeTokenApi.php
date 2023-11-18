<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\OAuth2;

use App\Controller\Api\BaseApi;
use App\Entity\Client;
use App\Service\OAuthTokenRevoker;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RevokeTokenApi extends BaseApi
{
    #[OA\Response(
        response: 204,
        description: 'Revoked the token',
        content: null,
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
        response: 403,
        description: 'You are not allowed to revoke this token',
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
    #[OA\Tag(name: 'oauth')]
    #[IsGranted('ROLE_USER')]
    /**
     * This API revokes any tokens associated with the authenticated user and client.
     */
    public function __invoke(
        EntityManagerInterface $entityManager,
        OAuthTokenRevoker $revoker,
        RateLimiterFactory $apiOauthTokenRevokeLimiter
    ) {
        $headers = $this->rateLimit($apiOauthTokenRevokeLimiter);

        $token = $this->container->get('security.token_storage')->getToken();
        $user = $this->getUserOrThrow();
        $client = $entityManager->getReference(Client::class, $token->getOAuthClientId());

        $revoker->revokeCredentialsForUserWithClient($user, $client);

        return new JsonResponse(
            status: 204,
            headers: $headers
        );
    }
}
