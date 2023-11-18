<?php

declare(strict_types=1);

namespace App\Kbin\User\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class UserBanResponseDto extends UserResponseDto implements \JsonSerializable
{
    public ?bool $isBanned = null;

    public function __construct(UserDto $dto, bool $isBanned)
    {
        parent::__construct($dto);
        $this->isBanned = $isBanned;
    }

    public function jsonSerialize(): mixed
    {
        $response = parent::jsonSerialize();
        $response['isBanned'] = $this->isBanned;

        return $response;
    }
}
