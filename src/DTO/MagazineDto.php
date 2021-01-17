<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class MagazineDto
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $name;

    /**
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $title;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}
