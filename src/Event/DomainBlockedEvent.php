<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Event;

use App\Entity\Domain;
use App\Entity\User;

class DomainBlockedEvent
{
    public function __construct(public Domain $domain, public User $user)
    {
    }
}
