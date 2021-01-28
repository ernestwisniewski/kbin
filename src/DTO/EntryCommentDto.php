<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Unique;
use App\Entity\Entry;

class EntryCommentDto
{
    private ?int $id = null;
    private Entry $entry;
    /**
     * @Assert\NotBlank()
     */
    private ?string $body;

    public function create(string $body, Entry $entry, ?int $id = null): self
    {
        $this->id    = $id;
        $this->body  = $body;
        $this->entry = $entry;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }

    public function setEntry(Entry $entry): void
    {
        $this->entry = $entry;
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
}
