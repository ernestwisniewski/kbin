<?php declare(strict_types=1);

namespace App\Message\ActivityPub\Outbox;

class DeliverMessage
{
    public function __construct(public string $apProfileId, public array $payload)
    {
    }
}
