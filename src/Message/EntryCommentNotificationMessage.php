<?php declare(strict_types=1);

namespace App\Message;

class EntryCommentNotificationMessage
{
    public function __construct(public int $commentId)
    {
    }
}
