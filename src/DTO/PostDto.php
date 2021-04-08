<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Magazine;
use App\Entity\Image;

class PostDto
{
    private ?int $id = null;
    /**
     * @Assert\NotBlank()
     */
    public ?Magazine $magazine = null;
    /**
     * @Assert\Length(
     *     min = 2,
     *     max = 15000
     * )
     */
    public ?string $body = null;
    public ?Image $image = null;
    public ?bool $isAdult = false;

    public function create(
        Magazine $magazine,
        ?string $body = null,
        ?Image $image = null,
        ?bool $isAdult = false,
        ?int $id = null
    ): self {
        $this->id       = $id;
        $this->magazine = $magazine;
        $this->body     = $body;
        $this->image    = $image;
        $this->isAdult  = $isAdult;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
