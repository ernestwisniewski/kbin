<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Magazine;
use App\Entity\Image;
use App\Entity\Entry;

class PostDto
{
    private ?int $id = null;
    /**
     * @Assert\NotBlank()
     */
    private ?Magazine $magazine = null;
    /**
     * @Assert\Length(
     *     min = 2,
     *     max = 15000
     * )
     */
    private ?string $body = null;
    private ?Image $image = null;

    public function create(Magazine $magazine, ?string $body = null, ?Image $image = null, ?int $id = null): self
    {
        $this->id       = $id;
        $this->magazine = $magazine;
        $this->body     = $body;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function setMagazine(Magazine $magazine): self
    {
        $this->magazine = $magazine;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }
}
