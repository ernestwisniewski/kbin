<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class PostCommentDeletedNotification extends Notification
{
    #[ManyToOne(targetEntity: PostComment::class, inversedBy: 'notifications')]
    #[JoinColumn]
    public ?PostComment $postComment = null;

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
        return 'post_comment_deleted_notification';
    }
}
