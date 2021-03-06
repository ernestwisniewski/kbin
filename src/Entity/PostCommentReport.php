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
    private PostComment $postComment;

    public function __construct(User $reporting, User $reported, PostComment $comment)
    {
        parent::__construct($reporting, $reported, $comment->getMagazine());

        $this->postComment = $comment;
    }

    public function getComment(): PostComment
    {
        return $this->postComment;
    }

    public function getType(): string
    {
        return 'post_comment';
    }
}

