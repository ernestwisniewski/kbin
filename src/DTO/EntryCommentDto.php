<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Image;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\EntryComment;
use App\Entity\Entry;

class EntryCommentDto
{
    private ?int $id = null;

    private Entry $entry;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 2,
     *     max = 4500
     * )
     */
    private ?string $body;

    private ?EntryComment $parent = null;

    private ?EntryComment $root = null;

    private ?Image $image = null;

    public function create(Entry $entry, string $body, ?Image $image = null, ?int $id = null): self
    {
        $this->id    = $id;
        $this->entry = $entry;
        $this->body  = $body;
        $this->image = $image;

        return $this;
    }

    public function createWithParent(Entry $entry, ?EntryComment $parent, ?Image $image = null, ?string $body = null): self
    {
        $this->entry  = $entry;
        $this->parent = $parent;
        $this->body   = $body;
        $this->image  = $image;

        if ($parent) {
            $this->root = $parent->getRoot() ?? $parent;
        }

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

    public function getParent(): ?EntryComment
    {
        return $this->parent;
    }

    public function setParent(?EntryComment $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getRoot(): ?EntryComment
    {
        return $this->root;
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
