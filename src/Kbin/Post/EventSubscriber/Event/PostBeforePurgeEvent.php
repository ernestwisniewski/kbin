<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post\EventSubscriber\Event;

use App\Entity\Post;
use App\Entity\User;

class PostBeforePurgeEvent
{
    public function __construct(public Post $post, public User $user)
    {
    }
}
