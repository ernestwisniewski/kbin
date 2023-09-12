<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

interface ActivityPubActorInterface
{
    public function getApName(): string;

    public function getPrivateKey(): ?string;

    public function getPublicKey(): ?string;
}
