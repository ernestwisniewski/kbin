<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Contracts\VotableInterface;
use App\Entity\Vote;

class VoteEvent
{
    public function __construct(
        public VotableInterface $votable,
        public Vote $vote,
        public bool $votedAgain,
        ?string $apId = null
    ) {
    }
}
