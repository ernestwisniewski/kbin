<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Image;
use App\Entity\Magazine;
use App\Entity\User;
use App\Utils\RegPatterns;
use App\Validator\Unique;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[Unique(Magazine::class, errorPath: 'name', fields: ['name'], idFields: ['id'])]
class MagazineDto
{
    public const MAX_NAME_LENGTH = 25;
    public const MAX_TITLE_LENGTH = 50;

    private User|UserDto|null $owner = null;
    public Image|ImageDto|null $icon = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: self::MAX_NAME_LENGTH)]
    #[Assert\Regex(pattern: RegPatterns::MAGAZINE_NAME, match: true)]
    public ?string $name = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: self::MAX_TITLE_LENGTH)]
    public ?string $title = null;
    #[Assert\Length(min: 3, max: Magazine::MAX_DESCRIPTION_LENGTH)]
    public ?string $description = null;
    #[Assert\Length(min: 3, max: Magazine::MAX_RULES_LENGTH)]
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
