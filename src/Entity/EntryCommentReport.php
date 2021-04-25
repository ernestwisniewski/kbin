<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class EntryCommentReport extends Report
{
    /**
     * @ORM\ManyToOne(targetEntity="EntryComment", inversedBy="reports")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?EntryComment $entryComment;

    public function __construct(User $reporting, User $reported, EntryComment $comment, ?string $reason = null)
    {
        parent::__construct($reporting, $reported, $comment->magazine, $reason);

        $this->entryComment = $comment;
    }

    public function getSubject(): EntryComment
    {
        return $this->entryComment;
    }

    public function clearSubject(): Report
    {
        $this->entryComment = null;

        return $this;
    }

    public function getType(): string
    {
        return 'entry_comment';
    }
}
