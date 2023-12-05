<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User\EventSubscriber\Event;

use App\Entity\User;

class UserFollowEvent
{
    public function __construct(public User $follower, public User $following, public $unfollow = false)
    {
    }
}
