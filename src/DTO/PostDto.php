<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class PostDto
{
    #[Assert\NotBlank]
    public Magazine|MagazineDto|null $magazine = null;
    public User|UserDto|null $user = null;
    public Image|ImageDto|null $image = null;
    #[Assert\Length(min: 2, max: 15000)]
    public ?string $body = null;
    public ?bool $isAdult = false;
    public ?string $slug = null;
    public ?int $comments = null;
    public ?int $uv = null;
    public ?int $dv = null;
    public ?int $score = null;
    public ?string $visibility = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTime $lastActive = null;
    public ?string $ip = null;
    private ?int $id = null;

    public function create(
        Magazine $magazine,
        User $user,
        ?Image $image = null,
        ?string $body = null,
        ?bool $isAdult = false,
        ?string $slug = null,
        ?int $comments = null,
        ?int $uv = null,
        ?int $dv = null,
        ?int $score = null,
        ?string $visibility = null,
        ?string $ip = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTime $lastActive = null,
        ?int $id = null
    ): self {
        $this->id         = $id;
        $this->magazine   = $magazine;
        $this->user       = $user;
        $this->body       = $body;
        $this->image      = $image;
        $this->isAdult    = $isAdult;
        $this->comments   = $comments;
        $this->slug       = $slug;
        $this->uv         = $uv;
        $this->dv         = $dv;
        $this->score      = $score;
        $this->visibility = $visibility;
        $this->ip         = $ip;
        $this->createdAt  = $createdAt;
        $this->lastActive  = $lastActive;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
