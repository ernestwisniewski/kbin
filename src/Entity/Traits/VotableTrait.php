<?php declare(strict_types = 1);

namespace App\Entity\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use App\Entity\Contracts\Votable;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Vote;
use App\Entity\User;

trait VotableTrait
{
    /**
     * @ORM\Column(type="integer")
     */
    private int $upVotes = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $downVotes = 0;

    public function getUpVotes(): Collection
    {
        $this->getVotes()->get(-1);

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('choice', self::VOTE_UP));

        return $this->getVotes()->matching($criteria);
    }

    public function getDownVotes(): Collection
    {
        $this->getVotes()->get(-1);

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('choice', self::VOTE_DOWN));

        return $this->getVotes()->matching($criteria);
    }

    public function countUpVotes(): int
    {
        return $this->upVotes;
    }

    public function countDownVotes(): int
    {
        return $this->downVotes;
    }

    public function countVotes(): int
    {
        return $this->votes->count();
    }

    public function getUserChoice(User $user): int
    {
        $vote = $this->getUserVote($user);

        return $vote ? $vote->getChoice() : Votable::VOTE_NONE;
    }

    public function getUserVote(User $user): ?Vote
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        return $this->getVotes()->matching($criteria)->first() ?: null;
    }

    public function updateVoteCounts(): self
    {
        $this->upVotes   = $this->getUpVotes()->count();
        $this->downVotes = $this->getDownVotes()->count();

        return $this;
    }
}
