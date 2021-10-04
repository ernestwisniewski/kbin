<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\User;
use App\Validator\Unique;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entityClass="App\Entity\Magazine", errorPath="name", fields={"name"}, idFields="id")
 */
class MagazineDto
{
    public User|UserDto|null $user = null;
    public Image|ImageDto|null $cover = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 25)]
    #[Assert\Regex(pattern: "/^[a-zA-Z0-9_]{2,25}$/", match: true)]
    public string $name;
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50)]
    public string $title;
    #[Assert\Length(min: 3, max: 420)]
    public ?string $description = null;
    #[Assert\Length(min: 3, max: 420)]
    public ?string $rules = null;
    public ?int $subscriptionsCount = null;
    public ?int $entryCount = null;
    public ?int $entryCommentCount = null;
    public ?int $postCount = null;
    public ?int $postCommentCount = null;
    public ?bool $isAdult = false;
    public Collection $badges;
    private ?int $id = null;

    public function create(
        string $name,
        string $title,
        Collection $badges,
        ?string $description = null,
        ?string $rules = null,
        ?User $user = null,
        ?Image $cover = null,
        ?int $subscriptionsCount = null,
        ?int $entryCount = null,
        ?int $entryCommentCount = null,
        ?int $postCount = null,
        ?int $postCommentCount = null,
        ?bool $isAdult = false,
        ?int $id = null
    ): self {
        $this->id                 = $id;
        $this->user               = $user;
        $this->user               = $user;
        $this->name               = $name;
        $this->title              = $title;
        $this->badges             = $badges;
        $this->description        = $description;
        $this->rules              = $rules;
        $this->cover              = $cover;
        $this->subscriptionsCount = $subscriptionsCount;
        $this->entryCount         = $entryCount;
        $this->entryCommentCount  = $entryCommentCount;
        $this->postCount          = $postCount;
        $this->postCommentCount   = $postCommentCount;
        $this->isAdult            = $isAdult;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
