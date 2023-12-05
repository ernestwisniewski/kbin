<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Vote\EventSubscriber\Event;

use App\Entity\Contracts\VotableInterface;
use App\Entity\Vote;

class VoteEvent
{
    public function __construct(
        public VotableInterface $votable,
        public Vote $vote,
        public bool $votedAgain,
        string $apId = null
    ) {
    }
}
