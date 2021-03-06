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
    private EntryComment $comment;

    public function __construct(User $reporting, EntryComment $comment) {
        parent::__construct($reporting);

        $this->comment = $comment;
    }

    public function getComment(): EntryComment {
        return $this->comment;
    }

    public function getType(): string {
        return 'entry_comment';
    }
}
