<?php

declare(strict_types=1);

// Copyright (c) Alex Bilbie
// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Security\OAuth;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant as LeagueClientCredentialsGrant;
use League\OAuth2\Server\RequestAccessTokenEvent;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Client credentials grant class. Modified to provide a bot user agent for the client.
 */
#[AsDecorator(LeagueClientCredentialsGrant::class)]
class ClientCredentialsGrant extends AbstractGrant
{
    protected EntityManagerInterface $entityManager;

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function getKbinClientEntityOrFail(string $clientId, ServerRequestInterface $request): Client
    {
        $clientEntityInterface = $this->getClientEntityOrFail($clientId, $request);

        $repository = $this->entityManager->getRepository(Client::class);

        /** @var ?Client $client */
        $client = $repository->findOneBy(['identifier' => $clientEntityInterface->getIdentifier()]);

        if (false === $client instanceof Client) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request));
            throw OAuthServerException::invalidClient($request);
        }

        if (null === $client->getUser()) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request));
            throw OAuthServerException::invalidClient($request);
        }

        return $client;
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        \DateInterval $accessTokenTTL
    ) {
        list($clientId) = $this->getClientCredentials($request);

        $client = $this->getKbinClientEntityOrFail($clientId, $request);

        if (!$client->isConfidential()) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request));

            throw OAuthServerException::invalidClient($request);
        }

        // Validate request
        $this->validateClient($request);

        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));

        // Finalize the requested scopes
        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client);

        // Issue and persist access token
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $client->getUser()->getUserIdentifier(), $finalizedScopes);

        // Send event to emitter
        $this->getEmitter()->emit(new RequestAccessTokenEvent(RequestEvent::ACCESS_TOKEN_ISSUED, $request, $accessToken));

        // Inject access token into response type
        $responseType->setAccessToken($accessToken);

        return $responseType;
    }

    public function getIdentifier()
    {
        return 'client_credentials';
    }
}
