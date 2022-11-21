<?php declare(strict_types=1);

namespace App\Message\ActivityPub\Inbox;

use App\Message\Contracts\AsyncApMessageInterface;
use App\Message\Contracts\AsyncMessageInterface;

class LikeMessage implements AsyncApMessageInterface
{
    public function __construct(public array $payload)
    {
    }
}
