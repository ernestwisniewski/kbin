<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Entry\EventSubscriber\Event;

use App\Entity\Entry;

class EntryHasBeenSeenEvent
{
    public function __construct(public Entry $entry)
    {
    }
}
