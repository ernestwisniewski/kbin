<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Client;
use App\Kbin\User\DTO\UserSmallResponseDto;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class ClientResponseDto implements \JsonSerializable
{
    public ?string $identifier = null;
    public ?string $name = null;
    public ?string $contactEmail = null;
    public ?string $description = null;
    public ?UserSmallResponseDto $user = null;
    public ?bool $active = null;
    public ?\DateTimeImmutable $createdAt = null;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string', format: 'uri'))]
    public ?array $redirectUris = null;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    public ?array $grants = null;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    public ?array $scopes = null;

    public function __construct(?Client $client)
    {
        if ($client) {
            $user = $client->getUser();
            $this->identifier = $client->getIdentifier();
            $this->name = $client->getName();
            $this->contactEmail = $client->getContactEmail();
            $this->description = $client->getDescription();
            $this->user = $user ? new UserSmallResponseDto($user) : null;
            $this->active = $client->isActive();
            $this->createdAt = $client->getCreatedAt();
            $this->redirectUris = array_map(fn (RedirectUri $uri) => (string) $uri, $client->getRedirectUris());
            $this->grants = array_map(fn (Grant $grant) => (string) $grant, $client->getGrants());
            $this->scopes = array_map(fn (Scope $scope) => (string) $scope, $client->getScopes());
        }
    }

    public function jsonSerialize(): mixed
    {
        return [
            'identifier' => $this->identifier,
            'name' => $this->name,
            'contactEmail' => $this->contactEmail,
            'description' => $this->description,
            'user' => $this->user?->jsonSerialize(),
            'active' => $this->active,
            'createdAt' => $this->createdAt->format(\DateTimeImmutable::ATOM),
            'redirectUris' => $this->redirectUris,
            'grants' => $this->grants,
            'scopes' => $this->scopes,
        ];
    }
}
