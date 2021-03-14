<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\EntryCommentNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EntryCommentNotificationRepository::class)
 */
class EntryCommentNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity="EntryComment", inversedBy="notifications")
     */
    private ?EntryComment $entryComment;

    public function __construct(User $receiver, EntryComment $comment)
    {
        parent::__construct($receiver);

        $this->entryComment = $comment;
    }

    public function getEntryComment(): EntryComment
    {
        return $this->entryComment;
    }

    public function getSubject(): EntryComment
    {
        return $this->entryComment;
    }

    public function getType(): string
    {
        return 'entry_comment_notification';
    }
}
