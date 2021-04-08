<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Unique;

/**
 * @Unique(entityClass="App\Entity\Magazine", errorPath="name", fields={"name"}, idFields="id")
 */
class MagazineDto
{
    private ?int $id = null;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 2,
     *     max = 25
     * )
     */
    public ?string $name;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 3,
     *     max = 50
     * )
     */
    public string $title;
    /**
     * @Assert\Length(
     *     min = 3,
     *     max = 420
     * )
     */
    public ?string $description = null;
    /**
     * @Assert\Length(
     *     min = 3,
     *     max = 420
     * )
     */
    public ?string $rules = null;
    public ?bool $isAdult = false;

    public function create(
        string $name,
        string $title,
        ?string $description = null,
        ?string $rules = null,
        ?bool $isAdult = false,
        ?int $id = null
    ): self {
        $this->id          = $id;
        $this->name        = $name;
        $this->title       = $title;
        $this->description = $description;
        $this->rules       = $rules;
        $this->isAdult     = $isAdult;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
