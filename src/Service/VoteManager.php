<?php declare(strict_types = 1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Contracts\Votable;
use App\Factory\VoteFactory;
use App\Entity\Vote;
use App\Entity\User;

class VoteManager
{
    private VoteFactory $voteFactory;
    private EntityManagerInterface $entityManager;

    public function __construct(VoteFactory $voteFactory, EntityManagerInterface $entityManager)
    {
        $this->voteFactory   = $voteFactory;
        $this->entityManager = $entityManager;
    }

    public function vote(int $choice, Votable $votable, User $user): Vote
    {
        $vote = $this->voteFactory->create($choice, $votable, $user);

        $this->entityManager->persist($vote);
        $this->entityManager->flush();

        return $vote;
    }
}
