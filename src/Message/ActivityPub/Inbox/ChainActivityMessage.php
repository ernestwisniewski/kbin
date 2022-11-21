<?php declare(strict_types=1);

namespace App\Message\ActivityPub\Inbox;

use App\Message\Contracts\AsyncApMessageInterface;
use App\Message\Contracts\AsyncMessageInterface;

class ChainActivityMessage implements AsyncApMessageInterface
{
    public function __construct(
        public array $chain,
        public ?array $parent = null,
        public ?array $announce = null,
        public ?array $like = null
    ) {
    }
}
