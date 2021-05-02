<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class EntryCommentDto
{
    public Magazine|MagazineDto|null $magazine;
    public User|UserDto|null $user = null;
    public Entry|EntryDto $entry;
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 5000)]
    public ?string $body;
    public ?EntryComment $parent = null;
    public ?EntryComment $root = null;
    public ?Image $image = null;
    private ?int $id = null;

    public function create(Entry $entry, string $body, ?Image $image = null, ?int $id = null): self
    {
        $this->id       = $id;
        $this->magazine = $entry->magazine;
        $this->user     = $entry->user;
        $this->entry    = $entry;
        $this->body     = $body;
        $this->image    = $image;

        return $this;
    }

    public function createWithParent(Entry $entry, ?EntryComment $parent, ?Image $image = null, ?string $body = null): self
    {
        $this->entry  = $entry;
        $this->parent = $parent;
        $this->body   = $body;
        $this->image  = $image;

        if ($parent) {
            $this->root = $parent->root ?? $parent;
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
