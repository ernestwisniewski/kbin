<?php declare(strict_types=1);

namespace App\Message\ActivityPub;

use App\Message\Contracts\AsyncMessageInterface;

class UpdateActorMessage implements AsyncMessageInterface
{
    public function __construct(public string $actorUrl)
    {
    }
}
