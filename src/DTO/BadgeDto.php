<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Magazine;

class BadgeDto
{
    private ?int $id = null;
    public ?Magazine $magazine = null;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 1,
     *     max = 20
     * )
     */
    public ?string $name = null;

    public function create(Magazine $magazine, ?int $id = null): self
    {
        $this->id       = $id;
        $this->magazine = $magazine;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
