<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\User;

class MagazineBanDto
{
    private ?int $id = null;
    private ?string $reason = null;
    private ?\DateTimeInterface $expiredAt = null;

    public function create(
        ?string $reason = null,
        ?\DateTimeInterface $expiredAt = null,
        ?int $id = null
    ) {
        $this->reason    = $reason;
        $this->expiredAt = $expiredAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    public function getExpiredAt(): ?\DateTimeInterface
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(?\DateTimeInterface $expiredAt): void
    {
        $this->expiredAt = $expiredAt;
    }
}
