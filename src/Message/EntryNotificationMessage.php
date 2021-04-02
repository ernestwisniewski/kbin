<?php declare(strict_types = 1);

namespace App\Message;

class EntryNotificationMessage
{
    public function __construct(private int $entryId)
    {
    }

    public function getEntryId(): int
    {
        return $this->entryId;
    }
}
