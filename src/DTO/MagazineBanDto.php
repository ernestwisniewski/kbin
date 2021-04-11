<?php declare(strict_types=1);

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
    ) {
        $this->reason    = $reason;
        $this->expiredAt = $expiredAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
