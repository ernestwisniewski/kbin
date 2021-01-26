<?php declare(strict_types = 1);

namespace App\Service;

use App\Factory\VoteFactory;
use App\Entity\Votable;
use App\Entity\User;

class VoteManager
{
    private VoteFactory $voteFactory;

    public function __construct(VoteFactory $voteFactory)
    {
        $this->voteFactory = $voteFactory;
    }

    public function vote(int $choice, Votable $votable, User $user)
    {
        $vote = $this->voteFactory->create($choice, $votable, $user);
        dd($vote);
    }
}
