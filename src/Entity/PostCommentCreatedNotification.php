<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class PostCommentCreatedNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity="PostComment", inversedBy="notifications")
     */
    public ?PostComment $postComment;

    public function __construct(User $receiver, PostComment $comment)
    {
        parent::__construct($receiver);

        $this->postComment = $comment;
    }

    public function getSubject(): PostComment
    {
        return $this->postComment;
    }

    public function getComment(): PostComment
    {
        return $this->postComment;
    }

    public function getType(): string
    {
        return 'post_comment_created_notification';
    }
}
