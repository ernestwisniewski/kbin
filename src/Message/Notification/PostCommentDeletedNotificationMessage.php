<?php declare(strict_types = 1);

namespace App\Message\Notification;

class PostCommentDeletedNotificationMessage
{
    public function __construct(public int $commentId)
    {
    }
}
