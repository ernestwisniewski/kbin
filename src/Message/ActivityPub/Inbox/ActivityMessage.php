<?php declare(strict_types=1);

namespace App\Message\ActivityPub\Inbox;

use App\Message\Contracts\AsyncMessageInterface;

class ActivityMessage implements AsyncMessageInterface
{
    public function __construct(public string $payload, public ?array $headers = null)
    {
    }
}
