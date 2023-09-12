<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Factory\EntryCommentFactory;
use App\Factory\EntryFactory;
use App\Factory\PostCommentFactory;
use App\Factory\PostFactory;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Ignore;

#[OA\Schema()]
class MagazineLogResponseDto implements \JsonSerializable
{
    public const LOG_TYPES = [
        'log_entry_deleted',
        'log_entry_restored',
        'log_entry_comment_deleted',
        'log_entry_comment_restored',
        'log_post_deleted',
        'log_post_restored',
        'log_post_comment_deleted',
        'log_post_comment_restored',
        'log_ban',
        'log_unban',
    ];

    #[OA\Property(enum: self::LOG_TYPES)]
    public ?string $type = null;
    public ?\DateTimeImmutable $createdAt = null;
    public ?MagazineSmallResponseDto $magazine = null;
    public ?UserSmallResponseDto $moderator = null;
    #[OA\Property(
        'subject',
        anyOf: [
            new OA\Schema(ref: new Model(type: EntryResponseDto::class)),
            new OA\Schema(ref: new Model(type: EntryCommentResponseDto::class)),
            new OA\Schema(ref: new Model(type: PostResponseDto::class)),
            new OA\Schema(ref: new Model(type: PostCommentResponseDto::class)),
            new OA\Schema(ref: new Model(type: MagazineBanResponseDto::class)),
        ],
    )]
    public ?\JsonSerializable $subject = null;

    public static function create(
        MagazineSmallResponseDto $magazine,
        UserSmallResponseDto $moderator,
        \DateTimeImmutable $createdAt,
        string $type,
        MagazineBanResponseDto $subject = null,
    ): self {
        $dto = new MagazineLogResponseDto();
        $dto->magazine = $magazine;
        $dto->moderator = $moderator;
        $dto->createdAt = $createdAt;
        $dto->type = $type;
        if ('log_ban' === $type || 'log_unban' === $type) {
            $dto->subject = $subject;
        }

        return $dto;
    }

    #[Ignore]
    public function setSubject(
        Entry|EntryComment|Post|PostComment|null $subject,
        EntryFactory $entryFactory,
        EntryCommentFactory $entryCommentFactory,
        PostFactory $postFactory,
        PostCommentFactory $postCommentFactory,
    ): void {
        switch ($this->type) {
            case 'log_entry_deleted':
            case 'log_entry_restored':
                assert($subject instanceof Entry);
                $this->subject = $entryFactory->createResponseDto($subject);
                break;
            case 'log_entry_comment_deleted':
            case 'log_entry_comment_restored':
                assert($subject instanceof EntryComment);
                $this->subject = $entryCommentFactory->createResponseDto($subject);
                break;
            case 'log_post_deleted':
            case 'log_post_restored':
                assert($subject instanceof Post);
                $this->subject = $postFactory->createResponseDto($subject);
                break;
            case 'log_post_comment_deleted':
            case 'log_post_comment_restored':
                assert($subject instanceof PostComment);
                $this->subject = $postCommentFactory->createResponseDto($subject);
                break;
            default:
                break;
        }
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => $this->type,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'magazine' => $this->magazine,
            'moderator' => $this->moderator,
            'subject' => $this->subject,
        ];
    }
}
