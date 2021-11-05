<?php declare(strict_types = 1);

namespace App\DTO;

class DomainDto
{
    public ?string $name = null;
    public ?int $entryCount = null;
    private ?int $id;

    public function create(string $name, ?int $entryCount, ?int $id = null): self
    {
        $this->id         = $id;
        $this->name       = $name;
        $this->entryCount = $entryCount;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
