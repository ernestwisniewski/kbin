<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api\OAuth2;

use App\Controller\Api\BaseApi;
use App\DTO\OAuth2ClientDto;
use Doctrine\ORM\EntityManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Service\CredentialsRevokerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeleteClientApi extends BaseApi
{
    #[OA\Response(
        response: 204,
        description: 'The client has been deactivated',
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
        response: 400,
        description: 'The operation does not apply to that client',
        content: new OA\JsonContent(ref: new Model(type: \App\Schema\Errors\BadRequestErrorSchema::class))
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
    #[OA\Parameter(name: 'client_id', in: 'query', schema: new OA\Schema(type: 'string'), required: true)]
    #[OA\Parameter(name: 'client_secret', in: 'query', schema: new OA\Schema(type: 'string'), required: true)]
    #[OA\Tag(name: 'oauth')]
    /**
     * This endpoint deactivates a client given their client_id and client_secret.
     *
     * This is useful if a confidential client has had their secret compromised and a
     * new client needs to be created. A public client cannot be deleted in this manner
     * since it does not have a secret to be compromised
     */
    public function __invoke(
        Request $request,
        ClientManagerInterface $manager,
        EntityManagerInterface $entityManager,
        CredentialsRevokerInterface $revoker,
        ValidatorInterface $validator,
        RateLimiterFactory $apiOauthClientDeleteLimiter
    ): JsonResponse {
        $headers = $this->rateLimit(anonLimiterFactory: $apiOauthClientDeleteLimiter);

        $dto = new OAuth2ClientDto(null);
        $dto->identifier = $request->get('client_id');
        $dto->secret = $request->get('client_secret');

        $validatorGroups = ['deleting'];
        $errors = $validator->validate($dto, groups: $validatorGroups);
        if (0 < \count($errors)) {
            throw new BadRequestHttpException((string) $errors);
        }

        $client = $manager->find($dto->identifier);
        if (null === $client || null === $client->getSecret()) {
            throw new BadRequestHttpException();
        }

        if (!hash_equals($client->getSecret(), $dto->secret)) {
            throw new BadRequestHttpException();
        }

        $client->setActive(false);
        $revoker->revokeCredentialsForClient($client);
        $entityManager->flush();

        return new JsonResponse(
            status: 204,
            headers: $headers
        );
    }
}
