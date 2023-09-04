<?php

declare(strict_types=1);

namespace App\DTO;

use App\DTO\Contracts\VisibilityAwareDtoTrait;
use App\Entity\Post;
use App\Entity\PostComment;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class PostCommentResponseDto implements \JsonSerializable
{
    use VisibilityAwareDtoTrait;

    public int $commentId;
    public ?UserSmallResponseDto $user = null;
    public ?MagazineSmallResponseDto $magazine = null;
    public ?int $postId = null;
    public ?int $parentId = null;
    public ?int $rootId = null;
    public ?ImageDto $image = null;
    public ?string $body = null;
    #[OA\Property(example: 'en', nullable: true)]
    public ?string $lang = null;
    public bool $isAdult = false;
    public ?int $uv = 0;
    public ?int $favourites = 0;
    public ?bool $isFavourited = null;
    public ?int $userVote = null;
    public ?string $apId = null;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    public ?array $mentions = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTimeImmutable $editedAt = null;
    public ?\DateTime $lastActive = null;
    public int $childCount = 0;
    #[OA\Property(
        type: 'array',
        description: 'Array of comments',
        items: new OA\Items(
            ref: '#/components/schemas/PostCommentResponseDto'
        ),
        example: [
            [
                'commentId' => 0,
                'userId' => 0,
                'magazineId' => 0,
                'postId' => 0,
                'parentId' => 0,
                'rootId' => 0,
                'image' => [
                    'filePath' => 'x/y/z.png',
                    'width' => 3000,
                    'height' => 4000,
                ],
                'body' => 'comment body',
                'lang' => 'en',
                'isAdult' => false,
                'uv' => 0,
                'favourites' => 0,
                'visibility' => 'visible',
                'apId' => 'string',
                'mentions' => [
                    '@user@instance',
                ],
                'createdAt' => '2023-06-18 11:59:41+00:00',
                'lastActive' => '2023-06-18 12:00:45+00:00',
                'childCount' => 0,
                'children' => [],
            ],
        ]
    )]
    public array $children = [];

    public static function create(
        int $id,
        UserSmallResponseDto $user = null,
        MagazineSmallResponseDto $magazine = null,
        Post $post = null,
        PostComment $parent = null,
        int $childCount = 0,
        ImageDto $image = null,
        string $body = null,
        string $lang = null,
        bool $isAdult = null,
        int $uv = null,
        int $favourites = null,
        string $visibility = null,
        string $apId = null,
        array $mentions = null,
        \DateTimeImmutable $createdAt = null,
        \DateTimeImmutable $editedAt = null,
        \DateTime $lastActive = null,
    ): self {
        $dto = new PostCommentResponseDto();
        $dto->commentId = $id;
        $dto->user = $user;
        $dto->magazine = $magazine;
        $dto->postId = $post->getId();
        $dto->parentId = $parent ? $parent->getId() : null;
        $dto->rootId = $parent ? ($parent->root ? $parent->root->getId() : $parent->getId()) : null;
        $dto->image = $image;
        $dto->body = $body;
        $dto->lang = $lang;
        $dto->isAdult = $isAdult;
        $dto->uv = $uv;
        $dto->favourites = $favourites;
        $dto->visibility = $visibility;
        $dto->apId = $apId;
        $dto->mentions = $mentions;
        $dto->createdAt = $createdAt;
        $dto->editedAt = $editedAt;
        $dto->lastActive = $lastActive;
        $dto->childCount = $childCount;

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
            'commentId' => $this->commentId,
            'user' => $this->user->jsonSerialize(),
            'magazine' => $this->magazine->jsonSerialize(),
            'postId' => $this->postId,
            'parentId' => $this->parentId,
            'rootId' => $this->rootId,
            'image' => $this->image?->jsonSerialize(),
            'body' => $this->body,
            'lang' => $this->lang,
            'isAdult' => $this->isAdult,
            'uv' => $this->uv,
            'favourites' => $this->favourites,
            'isFavourited' => $this->isFavourited,
            'userVote' => $this->userVote,
            'visibility' => $this->visibility,
            'apId' => $this->apId,
            'mentions' => $this->mentions,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'editedAt' => $this->editedAt?->format(\DateTimeInterface::ATOM),
            'lastActive' => $this->lastActive?->format(\DateTimeInterface::ATOM),
            'childCount' => $this->childCount,
            'children' => $this->children,
        ]);
    }

    public static function recursiveChildCount(int $initial, PostComment $child): int
    {
        return 1 + array_reduce($child->children->toArray(), self::class.'::recursiveChildCount', $initial);
    }
}
