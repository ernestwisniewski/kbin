<?php declare(strict_types=1);

namespace App\Message;

class EntryNotificationMessage
{
    public function __construct(public int $entryId)
    {
    }
}
