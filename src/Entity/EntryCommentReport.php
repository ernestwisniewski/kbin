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
    private ?EntryComment $subject;

    public function __construct(User $reporting, User $reported, EntryComment $comment, ?string $reason = null)
    {
        parent::__construct($reporting, $reported, $comment->getMagazine(), $reason);

        $this->subject = $comment;
    }

    public function getSubject(): EntryComment
    {
        return $this->subject;
    }

    public function clearSubject(): Report
    {
        $this->subject = null;

        return $this;
    }

    public function getType(): string
    {
        return EntryComment::class;
    }
}
