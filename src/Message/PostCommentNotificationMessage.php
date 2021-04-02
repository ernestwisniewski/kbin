<?php declare(strict_types = 1);

namespace App\Message;

class PostCommentNotificationMessage
{
    public function __construct(private int $commentId)
    {
    }

    public function getCommentId(): int
    {
        return $this->commentId;
    }
}
