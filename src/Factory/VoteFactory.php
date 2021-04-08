<?php declare(strict_types=1);

namespace App\Factory;

use App\Entity\Contracts\VoteInterface;
use App\Entity\EntryCommentVote;
use App\Entity\PostCommentVote;
use App\Entity\EntryComment;
use App\Entity\PostComment;
use App\Entity\EntryVote;
use App\Entity\PostVote;
use App\Entity\Entry;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Vote;
use LogicException;

class VoteFactory
{
    public function create(int $choice, VoteInterface $votable, User $user): Vote
    {
        if ($votable instanceof Entry) {
            $vote = new EntryVote($choice, $user, $votable);
        } elseif ($votable instanceof EntryComment) {
            $vote = new EntryCommentVote($choice, $user, $votable);
        } elseif ($votable instanceof Post) {
            $vote = new PostVote($choice, $user, $votable);
        } elseif ($votable instanceof PostComment) {
            $vote = new PostCommentVote($choice, $user, $votable);
        } else {
            throw new LogicException();
        }

        $votable->addVote($vote);

        return $vote;
    }
}
