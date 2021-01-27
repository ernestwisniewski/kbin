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
            return new EntryVote($choice, $user, $votable);
        }

        if ($votable instanceof EntryComment) {
            return new EntryCommentVote($choice, $user, $votable);
        }

        throw new \LogicException();
    }
}
