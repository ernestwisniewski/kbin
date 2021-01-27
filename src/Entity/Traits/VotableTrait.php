<?php declare(strict_types = 1);

namespace App\Entity\Traits;

use App\Entity\Contracts\Votable;
use App\Entity\User;
use App\Entity\Vote;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

trait VotableTrait
{
    public function getUpVotes(): Collection
    {
        return $this->votes;
    }

    public function getDownVotes(): Collection
    {
        return $this->votes;
    }

    public function countUpVotes(): int
    {
        $this->getVotes()->get(-1);

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('choice', self::VOTE_UP));

        return $this->getVotes()->matching($criteria)->count();
    }

    public function countDownVotes(): int
    {
        $this->getVotes()->get(-1);

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('choice', self::VOTE_DOWN));

        return $this->getVotes()->matching($criteria)->count();
    }

    public function getUserChoice(User $user): int {
        $vote = $this->getUserVote($user);

        return $vote ? $vote->getChoice() : Votable::VOTE_NONE;
    }

    public function getUserVote(User $user): ?Vote {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        return $this->getVotes()->matching($criteria)->first() ?: null;
    }
}
