<?php declare(strict_types = 1);

namespace App\Message\Notification;

use App\Message\AsyncMessageInterface;

class EntryDeletedNotificationMessage implements AsyncMessageInterface
{
    public function __construct(public int $entryId)
    {
    }
}
