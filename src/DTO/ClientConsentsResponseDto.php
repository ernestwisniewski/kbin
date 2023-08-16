<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class ClientConsentsResponseDto implements \JsonSerializable
{
    public ?int $consentId = null;
    public ?string $client = null;
    public ?string $description = null;
    public ?ImageDto $clientLogo = null;
    #[OA\Property(description: 'The scopes the app currently has permission to access', type: 'array', items: new OA\Items(type: 'string', enum: OAuth2ClientDto::AVAILABLE_SCOPES))]
    public ?array $scopesGranted = null;
    #[OA\Property(description: 'The scopes the app may request', type: 'array', items: new OA\Items(type: 'string', enum: OAuth2ClientDto::AVAILABLE_SCOPES))]
    public ?array $scopesAvailable = null;

    public static function create(int $consentId, string $clientName, ?string $clientDescription, ?ImageDto $logo, array $scopesGranted, array $scopesAvailable): self
    {
        $toReturn = new ClientConsentsResponseDto();
        $toReturn->consentId = $consentId;
        $toReturn->client = $clientName;
        $toReturn->description = $clientDescription;
        $toReturn->clientLogo = $logo;
        $toReturn->scopesGranted = $scopesGranted;
        $toReturn->scopesAvailable = $scopesAvailable;

        return $toReturn;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'consentId' => $this->consentId,
            'client' => $this->client,
            'description' => $this->description,
            'clientLogo' => $this->clientLogo?->jsonSerialize(),
            'scopesGranted' => $this->scopesGranted,
            'scopesAvailable' => $this->scopesAvailable,
        ];
    }
}
