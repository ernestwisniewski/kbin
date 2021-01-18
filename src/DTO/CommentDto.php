<?php declare(strict_types = 1);

namespace App\DTO;

use App\Entity\Entry;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Magazine;
use App\Entity\User;

class CommentDto
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var Entry|null
     */
    private $entry;

    /**
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $body;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Entry|null
     */
    public function getEntry(): ?Entry
    {
        return $this->entry;
    }

    /**
     * @param Entry|null $entry
     */
    public function setEntry(?Entry $entry): void
    {
        $this->entry = $entry;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string|null $body
     */
    public function setBody(?string $body): void
    {
        $this->body = $body;
    }
}
