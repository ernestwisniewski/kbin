<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\User;
use App\Utils\RegPatterns;
use App\Validator\Unique;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entityClass="App\Entity\Magazine", errorPath="name", fields={"name"}, idFields="id")
 */
class MagazineDto
{
    private User|UserDto|null $owner = null;
    public ?ImageDto $icon = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 25)]
    #[Assert\Regex(pattern: RegPatterns::MAGAZINE_NAME, match: true)]
    public ?string $name = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50)]
    public ?string $title = null;
    #[Assert\Length(min: 3, max: 10000)]
    public ?string $description = null;
    #[Assert\Length(min: 3, max: 10000)]
    public ?string $rules = null;
    public ?string $visibility = null;
    public int $subscriptionsCount = 0;
    public int $entryCount = 0;
    public int $entryCommentCount = 0;
    public int $postCount = 0;
    public int $postCommentCount = 0;
    public bool $isAdult = false;
    public ?bool $isUserSubscribed = null;
    public ?bool $isBlockedByUser = null;
    public ?array $tags = null;
    public ?Collection $badges = null;
    public ?Collection $moderators = null;
    public ?string $ip = null;
    public ?string $apId = null;
    public ?string $apProfileId = null;
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getOwner(): User|UserDto|null
    {
        return $this->owner;
    }

    public function setOwner(User|UserDto $owner): void
    {
        $this->owner = $owner;
    }
}
