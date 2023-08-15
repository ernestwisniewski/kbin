<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\EntryComment;
use App\Factory\EntryCommentFactory;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class EntryCommentResponseDto implements \JsonSerializable
{
    public int $commentId;
    public ?UserSmallResponseDto $user = null;
    public ?MagazineSmallResponseDto $magazine = null;
    public ?int $entryId = null;
    public ?int $parentId = null;
    public ?int $rootId = null;
    public ?ImageDto $image;
    public ?string $body = null;
    #[OA\Property(example: 'en', nullable: true)]
    public ?string $lang = null;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    public ?array $mentions = null;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    public ?array $tags = null;
    public int $uv = 0;
    public int $dv = 0;
    public int $favourites = 0;
    #[OA\Property(default: VisibilityInterface::VISIBILITY_VISIBLE, nullable: true, enum: [VisibilityInterface::VISIBILITY_PRIVATE, VisibilityInterface::VISIBILITY_TRASHED, VisibilityInterface::VISIBILITY_SOFT_DELETED, VisibilityInterface::VISIBILITY_VISIBLE])]
    public ?string $visibility = null;
    public bool $isAdult = false;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTimeImmutable $editedAt = null;
    public ?\DateTime $lastActive = null;
    public ?string $apId = null;
    #[OA\Property(
        type: 'array',
        description: 'Array of comments',
        items: new OA\Items(
            ref: new Model(type: EntryCommentResponseDto::class)
        ),
        example: [
            [
                'commentid' => 0,
                'user' => [
                    'userId' => 0,
                    'username' => 'test',
                ],
                'magazine' => [
                    'magazineId' => 0,
                    'name' => 'test',
                ],
                'entryId' => 0,
                'parentId' => 0,
                'rootId' => 0,
                'image' => [
                    'filePath' => 'x/y/z.png',
                    'width' => 3000,
                    'height' => 4000,
                ],
                'body' => 'string',
                'lang' => 'en',
                'isAdult' => false,
                'uv' => 0,
                'dv' => 0,
                'favourites' => 0,
                'visibility' => 'visible',
                'apId' => 'string',
                'mentions' => [
                    '@user@instance',
                ],
                'tags' => [
                    'string',
                ],
                'createdAt' => '2023-06-18 11:59:41-07:00',
                'editedAt' => '2023-06-18 11:59:41-07:00',
                'lastActive' => '2023-06-18 12:00:45-07:00',
                'childCount' => 0,
                'children' => [],
            ],
        ]
    )]
    public array $children = [];
    #[OA\Property(description: 'The total number of children the comment has.')]
    public int $childCount = 0;

    public function __construct(EntryCommentDto|EntryComment $dto, EntryComment $parent = null, int $childCount = 0)
    {
        if (null == $parent) {
            $parent = $dto->parent;
        }
        $this->commentId = $dto->getId();
        $this->user = new UserSmallResponseDto($dto->user);
        $this->magazine = new MagazineSmallResponseDto($dto->magazine);
        $this->entryId = $dto->entry->getId();
        $this->parentId = $parent ? $parent->getId() : null;
        $this->rootId = $parent ? ($parent->root ? $parent->root->getId() : $parent->getId()) : null;
        $this->image = $dto->image ? new ImageDto($dto->image) : null;
        $this->body = $dto->body;
        $this->lang = $dto->lang;
        $this->isAdult = $dto->isAdult;
        if ($dto instanceof EntryCommentDto) {
            $this->uv = $dto->uv;
            $this->dv = $dto->dv;
        } else {
            $this->uv = $dto->countUpVotes();
            $this->dv = $dto->countDownVotes();
        }
        $this->favourites = $dto->favouriteCount;
        $this->visibility = $dto->visibility;
        $this->apId = $dto->apId;
        $this->mentions = $dto->mentions;
        $this->tags = $dto->tags;
        $this->createdAt = $dto->createdAt;
        $this->editedAt = $dto->editedAt;
        $this->lastActive = $dto->lastActive;
        $this->childCount = $childCount;
    }

    private static function recursiveChildCount(int $initial, EntryComment $child): int
    {
        return 1 + array_reduce($child->children->toArray(), self::class.'::recursiveChildCount', $initial);
    }

    public static function fromTree(EntryComment $comment, EntryCommentFactory $factory, int $depth = -1): EntryCommentResponseDto
    {
        $toReturn = new EntryCommentResponseDto($factory->createDto($comment), $comment->parent, array_reduce($comment->children->toArray(), self::class.'::recursiveChildCount', 0));

        if (0 === $depth) {
            return $toReturn;
        }

        foreach ($comment->children as $childComment) {
            assert($childComment instanceof EntryComment);
            $child = self::fromTree($childComment, $factory, $depth > 0 ? $depth - 1 : -1);
            array_push($toReturn->children, $child);
        }

        return $toReturn;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'commentId' => $this->commentId,
            'user' => $this->user->jsonSerialize(),
            'magazine' => $this->magazine->jsonSerialize(),
            'entryId' => $this->entryId,
            'parentId' => $this->parentId,
            'rootId' => $this->rootId,
            'image' => $this->image?->jsonSerialize(),
            'body' => $this->body,
            'lang' => $this->lang,
            'isAdult' => $this->isAdult,
            'uv' => $this->uv,
            'dv' => $this->dv,
            'favourites' => $this->favourites,
            'visibility' => $this->visibility,
            'apId' => $this->apId,
            'mentions' => $this->mentions,
            'tags' => $this->tags,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'editedAt' => $this->editedAt->format(\DateTimeInterface::ATOM),
            'lastActive' => $this->lastActive?->format(\DateTimeInterface::ATOM),
            'childCount' => $this->childCount,
            'children' => array_map(fn (EntryCommentResponseDto $child) => $child->jsonSerialize(), $this->children),
        ];
    }
}
