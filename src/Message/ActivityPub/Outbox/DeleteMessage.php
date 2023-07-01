<?php

declare(strict_types=1);

namespace App\Message\ActivityPub\Outbox;

use App\Message\Contracts\AsyncApMessageInterface;

class DeleteMessage implements AsyncApMessageInterface
{
    public function __construct(public int $id, public string $type)
    {
    }
}
