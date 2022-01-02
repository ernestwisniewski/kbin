<?php declare(strict_types=1);

namespace App\Message\Notification;

class PostCommentEditedNotificationMessage
{
    public function __construct(public int $commentId)
    {
    }
}
