<?php declare(strict_types=1);

namespace App\DTO;

use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entityClass="App\Entity\Magazine", errorPath="name", fields={"name"}, idFields="id")
 */
class MagazineDto
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 2,
     *     max = 25
     * )
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9_]{2,25}$/",
     *     match=true
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
    private ?int $id = null;

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
