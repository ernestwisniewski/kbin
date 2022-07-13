<?php

namespace App\Entity\Contracts;

interface ActivityPubActorInterface
{
    public function getActivityPubId(): string;
}
