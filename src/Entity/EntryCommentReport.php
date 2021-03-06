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
     */
    private EntryComment $entryComment;

    public function __construct(User $reporting, User $reported, EntryComment $comment)
    {
        parent::__construct($reporting, $reported, $comment->getMagazine());

        $this->entryComment = $comment;
    }

    public function getComment(): EntryComment
    {
        return $this->entryComment;
    }

    public function getType(): string
    {
        return 'entry_comment';
    }
}
