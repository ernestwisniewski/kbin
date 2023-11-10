<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\EntryDto;
use App\DTO\EntryResponseDto;
use App\Entity\Badge;
use App\Entity\Entry;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class EntryFactory
{
    public function __construct(
        private readonly Security $security,
        private readonly ImageFactory $imageFactory,
        private readonly DomainFactory $domainFactory,
        private readonly MagazineFactory $magazineFactory,
        private readonly UserFactory $userFactory,
        private readonly BadgeFactory $badgeFactory,
    ) {
    }

    public function createFromDto(EntryDto $dto, User $user): Entry
    {
        return new Entry(
            $dto->title,
            $dto->url,
            $dto->body,
            $dto->magazine,
            $user,
            $dto->isAdult,
            $dto->isOc,
            $dto->lang,
            $dto->ip,
        );
    }

    public function createResponseDto(EntryDto|Entry $entry): EntryResponseDto
    {
        $dto = $entry instanceof Entry ? $this->createDto($entry) : $entry;
        $badges = $dto->badges ? array_map(fn (Badge $badge) => $this->badgeFactory->createDto($badge), $dto->badges->toArray()) : null;

        return EntryResponseDto::create(
            $dto->getId(),
            $this->magazineFactory->createSmallDto($dto->magazine),
            $this->userFactory->createSmallDto($dto->user),
            $dto->domain,
            $dto->title,
            $dto->url,
            $dto->image,
            $dto->body,
            $dto->lang,
            $dto->tags,
            $badges,
            $dto->comments,
            $dto->uv,
            $dto->dv,
            $dto->isPinned,
            $dto->visibility,
            $dto->favouriteCount,
            $dto->isOc,
            $dto->isAdult,
            $dto->createdAt,
            $dto->editedAt,
            $dto->lastActive,
            $dto->type,
            $dto->slug,
            $dto->apId
        );
    }

    public function createDto(Entry $entry): EntryDto
    {
        $dto = new EntryDto();

        $dto->magazine = $entry->magazine;
        $dto->user = $entry->user;
        $dto->image = $entry->image ? $this->imageFactory->createDto($entry->image) : null;
        $dto->domain = $entry->domain ? $this->domainFactory->createDto($entry->domain) : null;
        $dto->title = $entry->title;
        $dto->url = $entry->url;
        $dto->body = $entry->body;
        $dto->comments = $entry->commentCount;
        $dto->uv = $entry->countUpVotes();
        $dto->dv = $entry->countDownVotes();
        $dto->favouriteCount = $entry->favouriteCount;
        $dto->isAdult = $entry->isAdult;
        $dto->isOc = $entry->isOc;
        $dto->lang = $entry->lang;
        $dto->badges = $entry->badges;
        $dto->slug = $entry->slug;
        $dto->score = $entry->score;
        $dto->visibility = $entry->visibility;
        $dto->ip = $entry->ip;
        $dto->tags = $entry->tags;
        $dto->createdAt = $entry->createdAt;
        $dto->editedAt = $entry->editedAt;
        $dto->lastActive = $entry->lastActive;
        $dto->setId($entry->getId());
        $dto->isPinned = $entry->sticky;
        $dto->type = $entry->type;
        $dto->apId = $entry->apId;

        $currentUser = $this->security->getUser();
        // Only return the user's vote if permission to control voting has been given
        $dto->isFavourited = $this->security->isGranted('ROLE_OAUTH2_ENTRY:VOTE') ? $entry->isFavored($currentUser) : null;
        $dto->userVote = $this->security->isGranted('ROLE_OAUTH2_ENTRY:VOTE') ? $entry->getUserChoice($currentUser) : null;

        return $dto;
    }
}
