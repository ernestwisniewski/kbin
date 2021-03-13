<?php

namespace App\Entity;

use App\Repository\EntryCommentReportRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EntryCommentReportRepository::class)
 */
class EntryCommentReport extends Report
{
    /**
     * @ORM\ManyToOne(targetEntity="EntryComment", inversedBy="reports")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?EntryComment $entryComment;

    public function __construct(User $reporting, User $reported, EntryComment $comment, ?string $reason = null)
    {
        parent::__construct($reporting, $reported, $comment->getMagazine(), $reason);

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
