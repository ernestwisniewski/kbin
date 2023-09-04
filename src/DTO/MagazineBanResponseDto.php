<?php

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class MagazineBanResponseDto implements \JsonSerializable
{
    public ?int $banId = null;
    public ?string $reason = null;
    public ?\DateTimeInterface $expiredAt = null;
    public ?MagazineSmallResponseDto $magazine = null;
    public ?UserSmallResponseDto $bannedUser = null;
    public ?UserSmallResponseDto $bannedBy = null;

    public static function create(
        int $id,
        string $reason = null,
        \DateTimeInterface $expiredAt = null,
        MagazineSmallResponseDto $magazine = null,
        UserSmallResponseDto $user = null,
        UserSmallResponseDto $bannedBy = null,
    ): self {
        $dto = new MagazineBanResponseDto();
        $dto->reason = $reason;
        $dto->expiredAt = $expiredAt;
        $dto->magazine = $magazine;
        $dto->bannedUser = $user;
        $dto->bannedBy = $bannedBy;
        $dto->banId = $id;

        return $dto;
    }

    public function getExpired(): bool
    {
        return $this->expiredAt && (new \DateTime('+10 seconds')) >= $this->expiredAt;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'banId' => $this->banId,
            'reason' => $this->reason,
            'expired' => $this->getExpired(),
            'expiredAt' => $this->expiredAt?->format(\DateTimeInterface::ATOM),
            'bannedUser' => $this->bannedUser->jsonSerialize(),
            'bannedBy' => $this->bannedBy->jsonSerialize(),
            'magazine' => $this->magazine->jsonSerialize(),
        ];
    }
}
