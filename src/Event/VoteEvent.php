<?php declare(strict_types = 1);

namespace App\Event;

use App\Entity\Contracts\VoteInterface;

class VoteEvent
{
    public function __construct(public VoteInterface $votable)
    {
    }
}
