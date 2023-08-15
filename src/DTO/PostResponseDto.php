<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Post;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class PostResponseDto implements \JsonSerializable
{
    public int $postId;
    public ?UserSmallResponseDto $user = null;
    public ?MagazineSmallResponseDto $magazine = null;
    public ?ImageDto $image = null;
    public ?string $body = null;
    #[OA\Property(example: 'en', nullable: true, minLength: 2, maxLength: 3)]
    public ?string $lang = null;
    public bool $isAdult = false;
    public ?string $slug = null;
    public int $comments = 0;
    public int $uv = 0;
    public int $dv = 0;
    public int $score = 0;
    #[OA\Property(default: VisibilityInterface::VISIBILITY_VISIBLE, nullable: true, enum: [VisibilityInterface::VISIBILITY_PRIVATE, VisibilityInterface::VISIBILITY_TRASHED, VisibilityInterface::VISIBILITY_SOFT_DELETED, VisibilityInterface::VISIBILITY_VISIBLE])]
    public ?string $visibility = null;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    public ?array $tags = null;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    public ?array $mentions = null;
    public ?string $apId = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTimeImmutable $editedAt = null;
    public ?\DateTime $lastActive = null;

    public function __construct(PostDto|Post $dto)
    {
        $this->postId = $dto->getId();
        $this->user = new UserSmallResponseDto($dto->user);
        $this->magazine = new MagazineSmallResponseDto($dto->magazine);
        if ($dto->image) {
            $this->image = $dto->image instanceof ImageDto ? $dto->image : new ImageDto($dto->image);
        }
        $this->body = $dto->body;
        $this->lang = $dto->lang;
        $this->isAdult = $dto->isAdult;
        if ($dto instanceof PostDto) {
            $this->comments = $dto->comments;
            $this->uv = $dto->uv;
            $this->dv = $dto->dv;
        } else {
            $this->comments = $dto->getCommentCount();
            $this->uv = $dto->countUpVotes();
            $this->dv = $dto->countDownVotes();
        }
        $this->score = $dto->score;
        $this->visibility = $dto->visibility;
        $this->tags = $dto->tags;
        $this->mentions = $dto->mentions;
        $this->apId = $dto->apId;
        $this->createdAt = $dto->createdAt;
        $this->editedAt = $dto->editedAt;
        $this->lastActive = $dto->lastActive;
        $this->slug = $dto->slug;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'postId' => $this->postId,
            'user' => $this->user->jsonSerialize(),
            'magazine' => $this->magazine->jsonSerialize(),
            'image' => $this->image?->jsonSerialize(),
            'body' => $this->body,
            'lang' => $this->lang,
            'isAdult' => $this->isAdult,
            'comments' => $this->comments,
            'uv' => $this->uv,
            'dv' => $this->dv,
            'score' => $this->score,
            'visibility' => $this->visibility,
            'apId' => $this->apId,
            'tags' => $this->tags,
            'mentions' => $this->mentions,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'editedAt' => $this->editedAt?->format(\DateTimeInterface::ATOM),
            'lastActive' => $this->lastActive?->format(\DateTimeInterface::ATOM),
            'slug' => $this->slug,
        ];
    }
}
