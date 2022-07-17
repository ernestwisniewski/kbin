<?php

namespace App\Entity\Contracts;

interface ActivityPubActorInterface
{
    public function getActivityPubId(): string;

    public function getPrivateKey(): ?string;

    public function getPublicKey(): ?string;
}
