<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\MagazineLog;
use App\Entity\MagazineLogBan;
use App\Entity\Post;
use App\Entity\PostComment;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

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

    public function __construct(MagazineLog $log)
    {
        $this->magazine = new MagazineSmallResponseDto($log->magazine);
        $this->moderator = new UserSmallResponseDto($log->user);
        $this->createdAt = $log->createdAt;
        $this->type = $log->getType();
        $subject = $log->getSubject();
        switch ($this->type) {
            case 'log_entry_deleted':
            case 'log_entry_restored':
                /*
                 * @var Entry $subject
                 */
                $this->subject = new EntryResponseDto($subject);
                break;
            case 'log_entry_comment_deleted':
            case 'log_entry_comment_restored':
                /*
                 * @var EntryComment $subject
                 */
                $this->subject = new EntryCommentResponseDto($subject);
                break;
            case 'log_post_deleted':
            case 'log_post_restored':
                /*
                 * @var Post $subject
                 */
                $this->subject = new PostResponseDto($subject);
                break;
            case 'log_post_comment_deleted':
            case 'log_post_comment_restored':
                /*
                 * @var PostComment $subject
                 */
                $this->subject = new PostCommentResponseDto($subject);
                break;
            case 'log_ban':
                // $subject is null
                /*
                 * @var MagazineLogBan $log
                 */
                $this->subject = new MagazineBanResponseDto($log->ban);
                if ('unban' === $log->meta) {
                    $this->type = 'log_unban';
                }
                break;
        }
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => $this->type,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'magazine' => $this->magazine->jsonSerialize(),
            'moderator' => $this->moderator->jsonSerialize(),
            'subject' => $this->subject?->jsonSerialize(),
        ];
    }
}
