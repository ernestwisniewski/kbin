<?php declare(strict_types = 1);

namespace App\DTO;

use App\Entity\Image;
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
    public ?string $name = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50)]
    public ?string $title = null;
    #[Assert\Length(min: 3, max: 420)]
    public ?string $description = null;
    #[Assert\Length(min: 3, max: 420)]
    public ?string $rules = null;
    public int $subscriptionsCount = 0;
    public int $entryCount = 0;
    public int $entryCommentCount = 0;
    public int $postCount = 0;
    public int $postCommentCount = 0;
    public bool $isAdult = false;
    public ?Collection $badges = null;
    public ?string $ip = null;
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
