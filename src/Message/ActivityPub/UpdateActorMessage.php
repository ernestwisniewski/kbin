<?php

declare(strict_types=1);

namespace App\Message\ActivityPub;

use App\Message\Contracts\AsyncApMessageInterface;

class UpdateActorMessage implements AsyncApMessageInterface
{
    public function __construct(public string $actorUrl)
    {
    }
}
