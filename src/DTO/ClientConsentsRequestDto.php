<?php

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema()]
class ClientConsentsRequestDto
{
    #[Assert\NotBlank]
    #[OA\Property(description: 'The scopes the app has permission to access', type: 'array', items: new OA\Items(type: 'string', enum: OAuth2ClientDto::AVAILABLE_SCOPES))]
    public ?array $scopes = null;
}
