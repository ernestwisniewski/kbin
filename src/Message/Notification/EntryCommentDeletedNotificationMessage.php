<?php declare(strict_types = 1);

namespace App\Message\Notification;

class EntryCommentDeletedNotificationMessage
{
    public function __construct(public int $commentId)
    {
    }
}
