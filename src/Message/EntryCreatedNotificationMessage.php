<?php declare(strict_types=1);

namespace App\Message;

class EntryCreatedNotificationMessage
{
    public function __construct(public int $entryId)
    {
    }
}
