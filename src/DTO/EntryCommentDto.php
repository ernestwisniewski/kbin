<?php declare(strict_types = 1);

namespace App\DTO;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class EntryCommentDto
{
    public Magazine|MagazineDto|null $magazine = null;
    public User|UserDto|null $user = null;
    public Entry|EntryDto|null $entry = null;
    public ?EntryComment $parent = null;
    public ?EntryComment $root = null;
    public Image|ImageDto|null $image = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 5000)]
    public ?string $body = null;
    public ?int $uv = null;
    public ?int $dv = null;
    public ?string $ip = null;
    public ?DateTimeImmutable $createdAt = null;
    public ?DateTime $lastActive = null;
    private ?int $id = null;

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

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
