<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\OAuth2ClientDto;
use App\Entity\Client;
use App\Kbin\User\Factory\UserFactory;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;

class ClientFactory
{
    public function __construct(
        private readonly ImageFactory $imageFactory,
        private readonly UserFactory $userFactory,
    ) {
    }

    public function createDto(Client $client): OAuth2ClientDto
    {
        return OAuth2ClientDto::create(
            $client->getIdentifier(),
            $client->getSecret(),
            $client->getName(),
            $client->getUser() ? $this->userFactory->createSmallDto($client->getUser()) : null,
            $client->getContactEmail(),
            $client->getDescription(),
            array_map(fn (RedirectUri $redirectUri) => (string) $redirectUri, $client->getRedirectUris()),
            array_map(fn (Grant $grant) => (string) $grant, $client->getGrants()),
            array_map(fn (Scope $scope) => (string) $scope, $client->getScopes()),
            $client->getImage() ? $this->imageFactory->createDto($client->getImage()) : null,
        );
    }
}
