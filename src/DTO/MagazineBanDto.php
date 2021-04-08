<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\User;

class MagazineBanDto
{
    private ?int $id = null;
    public ?string $reason = null;
    public ?\DateTimeInterface $expiredAt = null;

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
}
