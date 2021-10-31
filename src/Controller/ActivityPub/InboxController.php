<?php declare(strict_types = 1);

namespace App\Controller\ActivityPub;

use App\ActivityPub\Server;

class InboxController
{
    public function __construct(private Server $server)
    {
    }

    public function __invoke()
    {
        ($this->server)('actor');
    }
}
