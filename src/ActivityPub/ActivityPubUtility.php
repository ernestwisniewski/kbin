<?php declare(strict_types=1);

namespace App\ActivityPub;

use App\Entity\Contracts\ActivityPubActorInterface;

class ActivityPubUtility
{
    public function getActivityPubId(ActivityPubActorInterface $actor): string
    {
        return '';
    }
}
