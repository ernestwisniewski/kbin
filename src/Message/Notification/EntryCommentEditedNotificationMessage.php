<?php declare(strict_types = 1);

namespace App\Message\Notification;

class EntryCommentEditedNotificationMessage
{
    public function __construct(public int $commentId)
    {
    }
}
