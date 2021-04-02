<?php declare(strict_types = 1);

namespace App\Message;

class EntryCommentNotificationMessage
{
    public function __construct(private int $commentId)
    {
    }

    public function getCommentId(): int
    {
        return $this->commentId;
    }
}
