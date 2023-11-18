<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity\Contracts;

use App\Entity\Domain;

interface DomainInterface
{
    public function getUrl();

    public function setDomain(Domain $domain): self;
}
