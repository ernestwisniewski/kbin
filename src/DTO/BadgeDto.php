<?php declare(strict_types = 1);

namespace App\DTO;

use App\Entity\Magazine;
use Symfony\Component\Validator\Constraints as Assert;

class BadgeDto
{
    public Magazine|MagazineDto|null $magazine = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 20)]
    public ?string $name = null;
    private ?int $id = null;

    public function create(Magazine $magazine, string $name, ?int $id = null): self
    {
        $this->id       = $id;
        $this->magazine = $magazine;
        $this->name     = $name;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
