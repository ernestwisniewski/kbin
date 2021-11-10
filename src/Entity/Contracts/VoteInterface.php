<?php declare(strict_types=1);

namespace App\Entity\Contracts;

use App\Entity\User;
use App\Entity\Vote;
use Doctrine\Common\Collections\Collection;

interface VoteInterface
{
    const VOTE_UP = 1;
    const VOTE_NONE = 0;
    const VOTE_DOWN = -1;

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
