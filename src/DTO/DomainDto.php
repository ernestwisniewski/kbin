<?php declare(strict_types=1);

namespace App\DTO;

class DomainDto
{
    private ?int $id;
    public string $name;
    public ?int $entryCount;

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
