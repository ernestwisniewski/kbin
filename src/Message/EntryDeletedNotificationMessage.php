<?php declare(strict_types=1);

namespace App\Message;

class EntryDeletedNotificationMessage
{
    public function __construct(public int $entryId)
    {
    }
}
