<?php declare(strict_types = 1);

namespace App\Message\Notification;

class EntryCreatedNotificationMessage
{
    public function __construct(public int $entryId)
    {
    }
}
