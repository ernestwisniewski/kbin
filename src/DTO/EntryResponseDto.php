<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Domain;
use App\Entity\Entry;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class EntryResponseDto implements \JsonSerializable
{
    public int $entryId;
    public ?MagazineSmallResponseDto $magazine = null;
    public ?UserSmallResponseDto $user = null;
    public ?DomainDto $domain = null;
    public ?string $title = null;
    public ?string $url = null;
    public ?ImageDto $image = null;
    public ?string $body = null;
    #[OA\Property(example: 'en', nullable: true)]
    public ?string $lang = null;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    public ?array $tags = null;
    public int $numComments;
    public int $uv = 0;
    public int $dv = 0;
    public int $score = 0;
    public bool $isOc = false;
    public bool $isAdult = false;
    public bool $isPinned = false;
    public int $views = 0;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTimeImmutable $editedAt = null;
    public ?\DateTime $lastActive = null;
    public ?string $visibility = VisibilityInterface::VISIBILITY_VISIBLE;
    public ?string $slug = null;
    public ?string $apId = null;

    public function __construct(EntryDto|Entry $dto)
    {
        $this->entryId = $dto->getId();
        $this->magazine = new MagazineSmallResponseDto($dto->magazine);
        $this->user = new UserSmallResponseDto($dto->user);
        $this->domain = $dto->domain instanceof Domain ? DomainDto::createFromDomain($dto->domain) : $dto->domain;
        $this->title = $dto->title;
        $this->url = $dto->url;
        if ($dto->image) {
            $this->image = $dto->image instanceof ImageDto ? $dto->image : new ImageDto($dto->image);
        }
        $this->body = $dto->body;
        $this->lang = $dto->lang;
        $this->tags = $dto->tags;
        if ($dto instanceof EntryDto) {
            $this->numComments = $dto->comments;
            $this->uv = $dto->uv;
            $this->dv = $dto->dv;
            $this->isPinned = $dto->isPinned;
        } else {
            $this->numComments = $dto->commentCount;
            $this->uv = $dto->countUpVotes();
            $this->dv = $dto->countDownVotes();
            $this->isPinned = $dto->sticky;
        }
        $this->visibility = $dto->getVisibility();
        $this->score = $dto->score;
        $this->isOc = $dto->isOc;
        $this->isAdult = $dto->isAdult;
        $this->views = $dto->views;
        $this->createdAt = $dto->createdAt;
        $this->editedAt = $dto->editedAt;
        $this->lastActive = $dto->lastActive;
        $this->slug = $dto->slug;
        $this->apId = $dto->apId;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'entryId' => $this->entryId,
            'magazine' => $this->magazine->jsonSerialize(),
            'user' => $this->user->jsonSerialize(),
            'domain' => $this->domain->jsonSerialize(),
            'title' => $this->title,
            'url' => $this->url,
            'image' => $this->image?->jsonSerialize(),
            'body' => $this->body,
            'lang' => $this->lang,
            'tags' => $this->tags,
            'numComments' => $this->numComments,
            'uv' => $this->uv,
            'dv' => $this->dv,
            'score' => $this->score,
            'isOc' => $this->isOc,
            'isAdult' => $this->isAdult,
            'isPinned' => $this->isPinned,
            'views' => $this->views,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'editedAt' => $this->editedAt?->format(\DateTimeInterface::ATOM),
            'lastActive' => $this->lastActive?->format(\DateTimeInterface::ATOM),
            'visibility' => $this->visibility,
            'slug' => $this->slug,
            'apId' => $this->apId,
        ];
    }
}
