<?php declare(strict_types = 1);

namespace App\Message;

class EntryCommentNotificationMessage
{
    private int $commentId;

    public function __construct(int $commentId)
    {
        $this->commentId = $commentId;
    }

    public function getCommentId(): int
    {
        return $this->commentId;
    }
}
