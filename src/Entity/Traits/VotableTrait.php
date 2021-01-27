<?php declare(strict_types = 1);

namespace App\Entity\Traits;

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
}
