<?php declare(strict_types=1);

namespace App\Message\ActivityPub\Outbox;

class UpdateMessage
{
    public function __construct(public int $id, public string $type)
    {
    }
}
