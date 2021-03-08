<?php

namespace App\Entity;

use App\Repository\PostCommentReportRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostCommentReportRepository::class)
 */
class PostCommentReport extends Report
{
    /**
     * @ORM\ManyToOne(targetEntity="PostComment", inversedBy="reports")
     */
    private PostComment $subject;

    public function __construct(User $reporting, User $reported, PostComment $comment, ?string $reason = null)
    {
        parent::__construct($reporting, $reported, $comment->getMagazine(), $reason);

        $this->subject = $comment;
    }

    public function getSubject(): PostComment
    {
        return $this->subject;
    }

    public function getType(): string
    {
        return PostComment::class;
    }
}
