<?php declare(strict_types = 1);

namespace App\Factory;

use App\Entity\EntryVote;
use App\Entity\Votable;
use App\Entity\Entry;
use App\Entity\User;
use App\Entity\Vote;

class VoteFactory
{
    public function create(int $choice, Votable $votable, User $user)
    {
        if ($votable instanceof Entry) {
            return new EntryVote($choice, $votable, $user);
        }

        throw new \LogicException();
    }
}
