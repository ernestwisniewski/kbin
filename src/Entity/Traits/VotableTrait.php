<?php declare(strict_types = 1);

namespace App\Entity\Traits;

use App\Entity\Contracts\VoteInterface;
use App\Entity\User;
use App\Entity\Vote;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

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

        return $vote ? $vote->choice : VoteInterface::VOTE_NONE;
    }

    public function getUserVote(User $user): ?Vote
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $user));

        return $this->votes->matching($criteria)->first() ?: null;
    }

    public function updateVoteCounts(): self
    {
        $this->upVotes   = $this->getUpVotes()->count();
        $this->downVotes = $this->getDownVotes()->count();

        return $this;
    }

    public function getUpVotes(): Collection
    {
        $this->votes->get(-1);

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('choice', self::VOTE_UP));

        return $this->votes->matching($criteria);
    }

    public function getDownVotes(): Collection
    {
        $this->votes->get(-1);

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('choice', self::VOTE_DOWN));

        return $this->votes->matching($criteria);
    }
}
