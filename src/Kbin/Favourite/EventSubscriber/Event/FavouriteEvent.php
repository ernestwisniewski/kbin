<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Favourite\EventSubscriber\Event;

use App\Entity\Contracts\FavouriteInterface;
use App\Entity\User;

class FavouriteEvent
{
    public function __construct(public FavouriteInterface $subject, public User $user, public bool $removeLike = false)
    {
    }
}
