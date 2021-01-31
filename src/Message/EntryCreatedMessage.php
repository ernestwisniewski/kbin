<?php declare(strict_types=1);

namespace App\Message;

class EntryCreatedMessage
{
    private int $entryId;

    public function __construct(int $entryId)
    {
        $this->entryId = $entryId;
    }

    public function getEntryId(): int
    {
        return $this->entryId;
    }

}
