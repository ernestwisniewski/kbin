<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NotificationRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="notification_type", type="text")
 * @ORM\DiscriminatorMap({
 *   "entry_created": "EntryCreatedNotification",
 *   "entry_edited": "EntryEditedNotification",
 *   "entry_deleted": "EntryDeletedNotification",
 *   "entry_mentioned": "EntryMentionedNotification",
 *   "entry_comment_created": "EntryCommentCreatedNotification",
 *   "entry_comment_edited": "EntryCommentEditedNotification",
 *   "entry_comment_reply": "EntryCommentReplyNotification",
 *   "entry_comment_deleted": "EntryCommentDeletedNotification",
 *   "entry_comment_mentioned": "EntryCommentMentionedNotification",
 *   "post_created": "PostCreatedNotification",
 *   "post_edited": "PostEditedNotification",
 *   "post_deleted": "PostDeletedNotification",
 *   "post_mentioned": "PostMentionedNotification",
 *   "post_comment_created": "PostCommentCreatedNotification",
 *   "post_comment_edited": "PostCommentEditedNotification",
 *   "post_comment_reply": "PostCommentReplyNotification",
 *   "post_comment_deleted": "PostCommentDeletedNotification",
 *   "post_comment_mentioned": "PostCommentMentionedNotification",
 *   "message": "MessageNotification",
 *   "ban": "MagazineBanNotification",
 * })
 */
abstract class Notification
{
    const STATUS_NEW = 'new';
    const STATUS_READ = 'read';

    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="notifications")
     */
    public User $user;
    /**
     * @ORM\Column(type="string")
     */
    public string $status = self::STATUS_NEW;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

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
