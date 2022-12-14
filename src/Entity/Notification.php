<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'notification_type', type: 'text')]
#[DiscriminatorMap([
    'entry_created' => 'EntryCreatedNotification',
    'entry_edited' => 'EntryEditedNotification',
    'entry_deleted' => 'EntryDeletedNotification',
    'entry_mentioned' => 'PostCommentMentionedNotification',
    'entry_comment_created' => 'EntryCommentCreatedNotification',
    'entry_comment_edited' => 'EntryCommentEditedNotification',
    'entry_comment_reply' => 'EntryCommentReplyNotification',
    'entry_comment_deleted' => 'EntryCommentDeletedNotification',
    'entry_comment_mentioned' => 'EntryCommentMentionedNotification',
    'post_created' => 'PostCreatedNotification',
    'post_edited' => 'PostEditedNotification',
    'post_deleted' => 'PostDeletedNotification',
    'post_mentioned' => 'PostMentionedNotification',
    'post_comment_created' => 'PostCommentCreatedNotification',
    'post_comment_edited' => 'PostCommentEditedNotification',
    'post_comment_reply' => 'PostCommentReplyNotification',
    'post_comment_deleted' => 'PostCommentDeletedNotification',
    'post_comment_mentioned' => 'PostCommentMentionedNotification',
    'message' => 'MessageNotification',
    'ban' => 'MagazineBanNotification',
])]
abstract class Notification
{
    const STATUS_NEW = 'new';
    const STATUS_READ = 'read';

    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'notifications')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public User $user;

    #[Column(type: 'string')]
    public string $status = self::STATUS_NEW;

    public function __construct(User $receiver)
    {
        $this->user = $receiver;

        $this->createdAtTraitConstruct();
    }

    public function getId(): int
    {
        return $this->id;
    }

    abstract public function getType(): string;
}
