<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity\Contracts;

interface ActivityPubActorInterface
{
    public function getApName(): string;

    public function getPrivateKey(): ?string;

    public function getPublicKey(): ?string;
}
