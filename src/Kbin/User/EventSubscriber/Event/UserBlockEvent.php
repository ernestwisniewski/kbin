<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User\EventSubscriber\Event;

use App\Entity\User;

class UserBlockEvent
{
    public function __construct(public User $blocker, public User $blocked)
    {
    }
}
