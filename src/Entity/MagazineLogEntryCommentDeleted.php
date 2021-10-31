<?php declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Contracts\ContentInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class MagazineLogEntryCommentDeleted extends MagazineLog
{
    /**
     * @ORM\ManyToOne(targetEntity="EntryComment")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?EntryComment $entryComment;

    public function __construct(EntryComment $comment, User $user)
    {
        parent::__construct($comment->magazine, $user);

        $this->entryComment = $comment;
    }

    public function getType(): string
    {
        return 'log_entry_comment_deleted';
    }

    public function getComment(): EntryComment
    {
        return $this->entryComment;
    }

    public function getSubject(): ContentInterface
    {
        return $this->entryComment;
    }

    public function clearSubject(): MagazineLog
    {
        $this->entryComment = null;

        return $this;
    }
}
