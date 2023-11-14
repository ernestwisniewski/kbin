<?php

declare(strict_types=1);

namespace App\DTO;

use App\DTO\Contracts\VisibilityAwareDtoTrait;
use App\Entity\EntryComment;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class EntryCommentResponseDto implements \JsonSerializable
{
    use VisibilityAwareDtoTrait;

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
    public ?int $uv = 0;
    public ?int $dv = 0;
    public ?int $favourites = 0;
    public ?bool $isFavourited = null;
    public ?int $userVote = null;
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

    public static function create(
        int $id = null,
        UserSmallResponseDto $user = null,
        MagazineSmallResponseDto $magazine = null,
        int $entryId = null,
        int $parentId = null,
        int $rootId = null,
        ImageDto $image = null,
        string $body = null,
        string $lang = null,
        bool $isAdult = null,
        int $uv = null,
        int $dv = null,
        int $favourites = null,
        string $visibility = null,
        string $apId = null,
        array $mentions = null,
        array $tags = null,
        \DateTimeImmutable $createdAt = null,
        \DateTimeImmutable $editedAt = null,
        \DateTime $lastActive = null,
        int $childCount = 0,
    ): self {
        $dto = new EntryCommentResponseDto();
        $dto->commentId = $id;
        $dto->user = $user;
        $dto->magazine = $magazine;
        $dto->entryId = $entryId;
        $dto->parentId = $parentId;
        $dto->rootId = $rootId;
        $dto->image = $image;
        $dto->body = $body;
        $dto->lang = $lang;
        $dto->isAdult = $isAdult;
        $dto->uv = $uv;
        $dto->dv = $dv;
        $dto->favourites = $favourites;
        $dto->visibility = $visibility;
        $dto->apId = $apId;
        $dto->mentions = $mentions;
        $dto->tags = $tags;
        $dto->createdAt = $createdAt;
        $dto->editedAt = $editedAt;
        $dto->lastActive = $lastActive;
        $dto->childCount = $childCount;

        return $dto;
    }

    public static function recursiveChildCount(int $initial, EntryComment $child): int
    {
        return 1 + array_reduce($child->children->toArray(), self::class.'::recursiveChildCount', $initial);
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
                'mentions',
            ];
        }

        return $this->handleDeletion([
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
            'isFavourited' => $this->isFavourited,
            'userVote' => $this->userVote,
            'visibility' => $this->getVisibility(),
            'apId' => $this->apId,
            'mentions' => $this->mentions,
            'tags' => $this->tags,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'editedAt' => $this->editedAt?->format(\DateTimeInterface::ATOM),
            'lastActive' => $this->lastActive?->format(\DateTimeInterface::ATOM),
            'childCount' => $this->childCount,
            'children' => $this->children,
        ]);
    }
}
