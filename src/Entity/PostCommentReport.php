<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class PostCommentReport extends Report
{
    /**
     * @ORM\ManyToOne(targetEntity="PostComment", inversedBy="reports")
     */
    public ?PostComment $postComment;

    public function __construct(User $reporting, PostComment $comment, ?string $reason = null)
    {
        parent::__construct($reporting, $comment->user, $comment->magazine, $reason);

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
