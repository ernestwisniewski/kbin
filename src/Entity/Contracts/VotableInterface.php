<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity\Contracts;

use App\Entity\User;
use App\Entity\Vote;
use Doctrine\Common\Collections\Collection;

interface VotableInterface
{
    public const VOTE_UP = 1;
    public const VOTE_NONE = 0;
    public const VOTE_DOWN = -1;
    public const VOTE_CHOICES = [
        self::VOTE_DOWN,
        self::VOTE_NONE,
        self::VOTE_UP,
    ];

    public function getId(): int;

    public function addVote(Vote $votable): self;

    public function removeVote(Vote $votable): self;

    public function getUpVotes(): Collection;

    public function getDownVotes(): Collection;

    public function countUpVotes(): int;

    public function countDownVotes(): int;

    public function countVotes(): int;

    public function getUserChoice(User $user): int;

    public function getUserVote(User $user): ?Vote;
}
