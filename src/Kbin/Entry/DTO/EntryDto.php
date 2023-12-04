<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry\DTO;

use App\DTO\ImageDto;
use App\Entity\Contracts\ContentVisibilityInterface;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Entity\User;
use App\Kbin\Domain\DTO\DomainDto;
use App\Kbin\Magazine\DTO\MagazineDto;
use App\Kbin\User\DTO\UserDto;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EntryDto implements ContentVisibilityInterface
{
    #[Assert\NotBlank]
    public Magazine|MagazineDto|null $magazine = null;
    public User|UserDto|null $user = null;
    public ?ImageDto $image = null;
    public ?string $imageAlt = null;
    public ?string $imageUrl = null;
    public ?DomainDto $domain = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public ?string $title = null;
    #[Assert\Url]
    public ?string $url = null;
    #[Assert\Length(max: Entry::MAX_BODY_LENGTH)]
    public ?string $body = null;
    public ?string $lang = null;
    public string $type = Entry::ENTRY_TYPE_ARTICLE;
    public int $comments = 0;
    public int $uv = 0;
    public int $dv = 0;
    public int $favouriteCount = 0;
    public ?bool $isFavourited = null;
    public ?int $userVote = null;
    public bool $isOc = false;
    public bool $isAdult = false;
    public bool $isPinned = false;
    public ?Collection $badges = null;
    public ?string $slug = null;
    public int $score = 0;
    public ?string $visibility = VisibilityInterface::VISIBILITY_VISIBLE;
    public ?string $ip = null;
    public ?string $apId = null;
    public ?array $tags = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTimeImmutable $editedAt = null;
    public ?\DateTime $lastActive = null;
    private ?int $id = null;

    #[Assert\Callback]
    public function validate(
        ExecutionContextInterface $context,
        $payload
    ) {
        if (empty($this->image)) {
            $image = Request::createFromGlobals()->files->filter('entry_image');
            if (\is_array($image)) {
                $image = $image['image'];
            } else {
                $image = $context->getValue()->image;
            }
        } else {
            $image = $this->image;
        }

        if (empty($this->body) && empty($this->url) && empty($image)) {
            $this->buildViolation($context, 'url');
            $this->buildViolation($context, 'body');
            $this->buildViolation($context, 'image');
        }
    }

    private function buildViolation(ExecutionContextInterface $context, $path)
    {
        $context->buildViolation('This value should not be blank.')
            ->atPath($path)
            ->addViolation();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getVisibility(): string
    {
        return trim($this->visibility);
    }

    public function isVisible(): bool
    {
        return VisibilityInterface::VISIBILITY_VISIBLE === $this->getVisibility();
    }

    public function isPrivate(): bool
    {
        return VisibilityInterface::VISIBILITY_PRIVATE === $this->getVisibility();
    }

    public function isSoftDeleted(): bool
    {
        return VisibilityInterface::VISIBILITY_SOFT_DELETED === $this->getVisibility();
    }

    public function isTrashed(): bool
    {
        return VisibilityInterface::VISIBILITY_TRASHED === $this->getVisibility();
    }

    public function getType(): string
    {
        if ($this->url) {
            return Entry::ENTRY_TYPE_LINK;
        }

        $type = Entry::ENTRY_TYPE_IMAGE;

        if ($this->body) {
            $type = Entry::ENTRY_TYPE_ARTICLE;
        }

        return $type;
    }
}
