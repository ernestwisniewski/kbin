<?php

declare(strict_types=1);

namespace App\DTO;

use App\DTO\Contracts\VisibilityAwareDtoTrait;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class PostResponseDto implements \JsonSerializable
{
    use VisibilityAwareDtoTrait;

    public int $postId;
    public ?UserSmallResponseDto $user = null;
    public ?MagazineSmallResponseDto $magazine = null;
    public ?ImageDto $image = null;
    public ?string $body = null;
    #[OA\Property(example: 'en', nullable: true, minLength: 2, maxLength: 3)]
    public ?string $lang = null;
    public bool $isAdult = false;
    public bool $isPinned = false;
    public ?string $slug = null;
    public int $comments = 0;
    public ?int $uv = 0;
    public ?int $dv = 0;
    public ?int $favourites = 0;
    public ?bool $isFavourited = null;
    public ?int $userVote = null;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    public ?array $tags = null;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    public ?array $mentions = null;
    public ?string $apId = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTimeImmutable $editedAt = null;
    public ?\DateTime $lastActive = null;

    public static function create(
        int $id,
        UserSmallResponseDto $user,
        MagazineSmallResponseDto $magazine,
        ImageDto $image = null,
        string $body = null,
        string $lang = null,
        bool $isAdult = null,
        bool $isPinned = false,
        int $comments = null,
        int $uv = null,
        int $dv = null,
        int $favouriteCount = null,
        string $visibility = null,
        array $tags = null,
        array $mentions = null,
        string $apId = null,
        \DateTimeImmutable $createdAt = null,
        \DateTimeImmutable $editedAt = null,
        \DateTime $lastActive = null,
        string $slug = null
    ): self {
        $dto = new PostResponseDto();
        $dto->postId = $id;
        $dto->user = $user;
        $dto->magazine = $magazine;
        $dto->image = $image;
        $dto->body = $body;
        $dto->lang = $lang;
        $dto->isAdult = $isAdult;
        $dto->isPinned = $isPinned;
        $dto->comments = $comments;
        $dto->uv = $uv;
        $dto->dv = $dv;
        $dto->favourites = $favouriteCount;
        $dto->visibility = $visibility;
        $dto->tags = $tags;
        $dto->mentions = $mentions;
        $dto->apId = $apId;
        $dto->createdAt = $createdAt;
        $dto->editedAt = $editedAt;
        $dto->lastActive = $lastActive;
        $dto->slug = $slug;

        return $dto;
    }

    public function jsonSerialize(): mixed
    {
        if (null === self::$keysToDelete) {
            self::$keysToDelete = [
                'image',
                'body',
                'tags',
                'uv',
                'dv',
                'favourites',
                'isFavourited',
                'userVote',
                'slug',
                'mentions',
            ];
        }

        return $this->handleDeletion([
            'postId' => $this->postId,
            'user' => $this->user,
            'magazine' => $this->magazine,
            'image' => $this->image,
            'body' => $this->body,
            'lang' => $this->lang,
            'isAdult' => $this->isAdult,
            'isPinned' => $this->isPinned,
            'comments' => $this->comments,
            'uv' => $this->uv,
            'dv' => $this->dv,
            'favourites' => $this->favourites,
            'isFavourited' => $this->isFavourited,
            'userVote' => $this->userVote,
            'visibility' => $this->getVisibility(),
            'apId' => $this->apId,
            'tags' => $this->tags,
            'mentions' => $this->mentions,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'editedAt' => $this->editedAt?->format(\DateTimeInterface::ATOM),
            'lastActive' => $this->lastActive?->format(\DateTimeInterface::ATOM),
            'slug' => $this->slug,
        ]);
    }
}
