<?php declare(strict_types = 1);

namespace App\DTO;

use DateTimeInterface;

class MagazineBanDto
{
    public ?string $reason = null;
    public ?DateTimeInterface $expiredAt = null;
    private ?int $id = null;

    public function create(
        ?string $reason = null,
        ?DateTimeInterface $expiredAt = null,
        ?int $id = null
    ): self {
        $this->reason    = $reason;
        $this->expiredAt = $expiredAt;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
