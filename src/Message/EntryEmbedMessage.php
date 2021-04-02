<?php declare(strict_types = 1);

namespace App\Message;

class EntryEmbedMessage
{
    public function __construct(private int $entryId)
    {
    }

    public function getEntryId(): int
    {
        return $this->entryId;
    }
}
