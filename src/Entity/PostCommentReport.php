<?php declare(strict_types=1);

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
    public ?PostComment $postComment;

    public function __construct(User $reporting, User $reported, PostComment $comment, ?string $reason = null)
    {
        parent::__construct($reporting, $reported, $comment->magazine, $reason);

        $this->postComment = $comment;
    }

    public function getSubject(): PostComment
    {
        return $this->postComment;
    }

    public function clearSubject(): Report
    {
        $this->postComment = null;

        return $this;
    }

    public function getType(): string
    {
        return 'post_comment';
    }
}
