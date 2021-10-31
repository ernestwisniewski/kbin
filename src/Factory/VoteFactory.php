<?php declare(strict_types = 1);

namespace App\Factory;

use App\Entity\Contracts\VoteInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\EntryCommentVote;
use App\Entity\EntryVote;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostCommentVote;
use App\Entity\PostVote;
use App\Entity\User;
use App\Entity\Vote;
use LogicException;

class VoteFactory
{
    public function create(int $choice, VoteInterface $votable, User $user): Vote
    {
        $vote = match (true) {
            $votable instanceof Entry => new EntryVote($choice, $user, $votable),
            $votable instanceof EntryComment => new EntryCommentVote($choice, $user, $votable),
            $votable instanceof Post => new PostVote($choice, $user, $votable),
            $votable instanceof PostComment => new PostCommentVote($choice, $user, $votable),
            default => throw new LogicException(),
        };

        $votable->addVote($vote);

        return $vote;
    }
}
