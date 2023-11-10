<?php

declare(strict_types=1);

namespace App\DTO;

use App\DTO\Contracts\VisibilityAwareDtoTrait;
use App\Entity\Entry;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class EntryResponseDto implements \JsonSerializable
{
    use VisibilityAwareDtoTrait;

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
    #[OA\Property(type: 'array', description: 'Not implemented currently.', items: new OA\Items(ref: new Model(type: BadgeResponseDto::class)))]
    public ?array $badges = null;
    public int $numComments;
    public ?int $uv = 0;
    public ?int $dv = 0;
    public ?int $favourites = 0;
    public ?bool $isFavourited = null;
    public ?int $userVote = null;
    public bool $isOc = false;
    public bool $isAdult = false;
    public bool $isPinned = false;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTimeImmutable $editedAt = null;
    public ?\DateTime $lastActive = null;
    #[OA\Property(example: Entry::ENTRY_TYPE_ARTICLE, enum: Entry::ENTRY_TYPE_OPTIONS)]
    public ?string $type = null;
    public ?string $slug = null;
    public ?string $apId = null;

    public static function create(
        int $id = null,
        MagazineSmallResponseDto $magazine = null,
        UserSmallResponseDto $user = null,
        DomainDto $domain = null,
        string $title = null,
        string $url = null,
        ImageDto $image = null,
        string $body = null,
        string $lang = null,
        array $tags = null,
        array $badges = null,
        int $comments = null,
        int $uv = null,
        int $dv = null,
        bool $isPinned = null,
        string $visibility = null,
        int $favouriteCount = null,
        bool $isOc = null,
        bool $isAdult = null,
        \DateTimeImmutable $createdAt = null,
        \DateTimeImmutable $editedAt = null,
        \DateTime $lastActive = null,
        string $type = null,
        string $slug = null,
        string $apId = null
    ): self {
        $dto = new EntryResponseDto();
        $dto->entryId = $id;
        $dto->magazine = $magazine;
        $dto->user = $user;
        $dto->domain = $domain;
        $dto->title = $title;
        $dto->url = $url;
        $dto->image = $image;
        $dto->body = $body;
        $dto->lang = $lang;
        $dto->tags = $tags;
        $dto->badges = $badges;
        $dto->numComments = $comments;
        $dto->uv = $uv;
        $dto->dv = $dv;
        $dto->isPinned = $isPinned;
        $dto->visibility = $visibility;
        $dto->favourites = $favouriteCount;
        $dto->isOc = $isOc;
        $dto->isAdult = $isAdult;
        $dto->createdAt = $createdAt;
        $dto->editedAt = $editedAt;
        $dto->lastActive = $lastActive;
        $dto->type = $type;
        $dto->slug = $slug;
        $dto->apId = $apId;

        return $dto;
    }

    public function jsonSerialize(): mixed
    {
        if (null === self::$keysToDelete) {
            self::$keysToDelete = [
                'domain',
                'title',
                'url',
                'image',
                'body',
                'tags',
                'badges',
                'uv',
                'dv',
                'favourites',
                'isFavourited',
                'userVote',
                'slug',
            ];
        }

        return $this->handleDeletion([
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
            'badges' => $this->badges,
            'numComments' => $this->numComments,
            'uv' => $this->uv,
            'dv' => $this->dv,
            'favourites' => $this->favourites,
            'isFavourited' => $this->isFavourited,
            'userVote' => $this->userVote,
            'isOc' => $this->isOc,
            'isAdult' => $this->isAdult,
            'isPinned' => $this->isPinned,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'editedAt' => $this->editedAt?->format(\DateTimeInterface::ATOM),
            'lastActive' => $this->lastActive?->format(\DateTimeInterface::ATOM),
            'visibility' => $this->visibility,
            'type' => $this->type,
            'slug' => $this->slug,
            'apId' => $this->apId,
        ]);
    }
}
