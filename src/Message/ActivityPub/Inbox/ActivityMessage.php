<?php declare(strict_types=1);

namespace App\Message\ActivityPub\Inbox;

class ActivityMessage
{
    public function __construct(public string $payload, public ?array $headers = null)
    {
    }
}
