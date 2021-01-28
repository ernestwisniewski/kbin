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
     */
    private string $name;
    /**
     * @Assert\NotBlank()
     */
    private string $title;

    public function create(string $name, string $title, ?int $id = null): self
    {
        $this->id = $id;
        $this->name  = $name;
        $this->title = $title;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
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
}
