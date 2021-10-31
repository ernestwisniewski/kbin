<?php declare(strict_types = 1);

namespace App\Message\Notification;

class EntryCommentCreatedNotificationMessage
{
    public function __construct(public int $commentId)
    {
    }
}
