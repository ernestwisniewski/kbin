<?php declare(strict_types=1);

namespace App\Message\ActivityPub\Outbox;

use App\Entity\Contracts\ActivityPubActivityInterface;

class CreateMessage
{
    public function __construct(public ActivityPubActivityInterface $activity)
    {
    }
}
