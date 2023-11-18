<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Event\Magazine;

use App\Entity\Magazine;
use App\Entity\User;

class MagazineBlockedEvent
{
    public function __construct(public Magazine $magazine, public User $user)
    {
    }
}
