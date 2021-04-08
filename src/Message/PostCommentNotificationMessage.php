<?php declare(strict_types=1);

namespace App\Message;

class PostCommentNotificationMessage
{
    public function __construct(public int $commentId)
    {
    }
}
