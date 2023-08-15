<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\PostComment;
use App\Factory\PostCommentFactory;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class PostCommentResponseDto implements \JsonSerializable
{
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
    public int $uv = 0;
    public int $favourites = 0;
    #[OA\Property(default: VisibilityInterface::VISIBILITY_VISIBLE, nullable: true, enum: [VisibilityInterface::VISIBILITY_PRIVATE, VisibilityInterface::VISIBILITY_TRASHED, VisibilityInterface::VISIBILITY_SOFT_DELETED, VisibilityInterface::VISIBILITY_VISIBLE])]
    public ?string $visibility = null;
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

    public function __construct(PostCommentDto|PostComment $dto, PostComment $parent = null, int $childCount = 0)
    {
        $this->commentId = $dto->getId();
        $this->user = new UserSmallResponseDto($dto->user);
        $this->magazine = new MagazineSmallResponseDto($dto->magazine);
        $this->postId = $dto->post->getId();
        $this->parentId = $parent ? $parent->getId() : null;
        $this->rootId = $parent ? ($parent->root ? $parent->root->getId() : $parent->getId()) : null;
        if ($dto->image) {
            $this->image = $dto->image instanceof ImageDto ? $dto->image : new ImageDto($dto->image);
        }
        $this->body = $dto->body;
        $this->lang = $dto->lang;
        $this->isAdult = $dto->isAdult;
        if ($dto instanceof PostCommentDto) {
            $this->uv = $dto->uv;
            $this->favourites = $dto->favourites;
        } else {
            $this->uv = $dto->countUpVotes();
            $this->favourites = $dto->favourites->count();
        }
        $this->visibility = $dto->visibility;
        $this->apId = $dto->apId;
        $this->mentions = $dto->mentions;
        $this->createdAt = $dto->createdAt;
        $this->editedAt = $dto->editedAt;
        $this->lastActive = $dto->lastActive;
        $this->childCount = $childCount;
    }

    public function jsonSerialize(): mixed
    {
        return [
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
            'visibility' => $this->visibility,
            'apId' => $this->apId,
            'mentions' => $this->mentions,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'lastActive' => $this->lastActive?->format(\DateTimeInterface::ATOM),
            'childCount' => $this->childCount,
            'children' => $this->children,
        ];
    }

    private static function recursiveChildCount(int $initial, PostComment $child): int
    {
        return 1 + array_reduce($child->children->toArray(), self::class.'::recursiveChildCount', $initial);
    }

    public static function fromTree(PostComment $comment, PostCommentFactory $factory, int $depth): PostCommentResponseDto
    {
        $toReturn = new PostCommentResponseDto($factory->createDto($comment), $comment->parent, array_reduce($comment->children->toArray(), self::class.'::recursiveChildCount', 0));

        if (0 === $depth) {
            return $toReturn;
        }

        foreach ($comment->children as $childComment) {
            assert($childComment instanceof PostComment);
            $child = self::fromTree($childComment, $factory, $depth > 0 ? $depth - 1 : -1);
            array_push($toReturn->children, $child);
        }

        return $toReturn;
    }
}
