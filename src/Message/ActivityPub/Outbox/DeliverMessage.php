<?php declare(strict_types=1);

namespace App\Message\ActivityPub\Outbox;

use App\Message\Contracts\AsyncMessageInterface;

class DeliverMessage implements AsyncMessageInterface
{
    public function __construct(public string $apProfileId, public array $payload)
    {
    }
}
