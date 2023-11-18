<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Vote\Factory;

use App\Entity\Contracts\VotableInterface;
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

readonly class VoteFactory
{
    public function create(int $choice, VotableInterface $votable, User $user): Vote
    {
        $vote = match (true) {
            $votable instanceof Entry => new EntryVote($choice, $user, $votable),
            $votable instanceof EntryComment => new EntryCommentVote($choice, $user, $votable),
            $votable instanceof Post => new PostVote($choice, $user, $votable),
            $votable instanceof PostComment => new PostCommentVote($choice, $user, $votable),
            default => throw new \LogicException(),
        };

        $votable->addVote($vote);

        return $vote;
    }
}
