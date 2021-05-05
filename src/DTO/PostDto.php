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
    public ?string $ip = null;
    private ?int $id = null;

    public function create(
        Magazine $magazine,
        User $user,
        ?Image $image = null,
        ?string $body = null,
        ?bool $isAdult = false,
        ?string $slug = null,
        ?string $ip = null,
        ?int $id = null
    ): self {
        $this->id       = $id;
        $this->magazine = $magazine;
        $this->user     = $user;
        $this->body     = $body;
        $this->image    = $image;
        $this->isAdult  = $isAdult;
        $this->slug     = $slug;
        $this->ip       = $ip;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
