<?php declare(strict_types = 1);

namespace App\Message;

class EntryEmbedMessage
{
    public function __construct(public int $entryId)
    {
    }
}
