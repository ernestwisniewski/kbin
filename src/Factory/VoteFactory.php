<?php declare(strict_types = 1);

namespace App\Factory;

use App\Entity\Contracts\Votable;
use App\Entity\EntryComment;
use App\Entity\EntryCommentVote;
use App\Entity\EntryVote;
use App\Entity\Entry;
use App\Entity\User;
use App\Entity\Vote;

class VoteFactory
{
    public function create(int $choice, Votable $votable, User $user): Vote
    {
        if ($votable instanceof Entry) {
            $vote = new EntryVote($choice, $user, $votable);
            $votable->addVote($vote);

            return $vote;
        }

        if ($votable instanceof EntryComment) {
            $vote = new EntryCommentVote($choice, $user, $votable);
            $votable->addVote($vote);

            return $vote;
        }

        throw new \LogicException();
    }
}
