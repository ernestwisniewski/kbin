<?php declare(strict_types = 1);

namespace App\Message\Notification;

class EntryDeletedNotificationMessage
{
    public function __construct(public int $entryId)
    {
    }
}
