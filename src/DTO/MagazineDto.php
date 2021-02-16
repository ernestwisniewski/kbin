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
     *     min = 3,
     *     max = 24
     * )
     */
    private ?string $name;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 3,
     *     max = 50
     * )
     */
    private string $title;
    /**
     * @Assert\Length(
     *     min = 3,
     *     max = 420
     * )
     */
    private ?string $description = null;
    /**
     * @Assert\Length(
     *     min = 3,
     *     max = 420
     * )
     */
    private ?string $rules = null;

    public function create(string $name, string $title, ?string $description = null, ?string $rules = null, ?int $id = null): self
    {
        $this->id          = $id;
        $this->name        = $name;
        $this->title       = $title;
        $this->description = $description;
        $this->rules       = $rules;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getRules(): ?string
    {
        return $this->rules;
    }

    public function setRules(?string $rules): void
    {
        $this->rules = $rules;
    }
}
