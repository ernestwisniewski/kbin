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
    private PostComment $comment;

    public function __construct(User $reporting, PostComment $comment) {
        parent::__construct($reporting);

        $this->comment = $comment;
    }

    public function getComment(): PostComment {
        return $this->comment;
    }

    public function getType(): string {
        return 'post_comment';
    }
}

