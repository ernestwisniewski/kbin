<?php declare(strict_types=1);

namespace App\Message\ActivityPub\Inbox;

class ChainActivityMessage
{
    public function __construct(public array $chain, public ?array $parent = null)
    {
    }
}
