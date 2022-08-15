<?php declare(strict_types=1);

namespace App\Message\ActivityPub\Outbox;

class AnnounceMessage
{
    public function __construct(public int $userId, public int $objectId, public string $objectType, public \DateTimeInterface $createdAt)
    {
    }
}
