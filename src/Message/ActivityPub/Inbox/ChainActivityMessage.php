<?php declare(strict_types=1);

namespace App\Message\ActivityPub\Inbox;

use App\Message\AsyncMessageInterface;

class ChainActivityMessage implements AsyncMessageInterface
{
    public function __construct(public array $chain, public ?array $parent = null, public ?array $announce = null)
    {
    }
}
