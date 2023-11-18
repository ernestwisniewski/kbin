<?php

declare(strict_types=1);

namespace App\Kbin\Magazine\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Ignore;

#[OA\Schema()]
class MagazineBanDto
{
    public ?string $reason = null;
    public ?\DateTimeInterface $expiredAt = null;
    #[Ignore]
    private ?int $id = null;

    public static function create(
        string $reason = null,
        \DateTimeInterface $expiredAt = null,
        int $id = null
    ): self {
        $dto = new MagazineBanDto();
        $dto->reason = $reason;
        $dto->expiredAt = $expiredAt;

        return $dto;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
