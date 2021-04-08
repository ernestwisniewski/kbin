<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\PostCommentNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostCommentNotificationRepository::class)
 */
class PostCommentNotification extends Notification
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
        return 'post_comment_notification';
    }
}
