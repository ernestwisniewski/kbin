<?php declare(strict_types = 1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Contracts\VoteInterface;
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

    public function vote(int $choice, VoteInterface $votable, User $user): Vote
    {
        $vote = $votable->getUserVote($user);

        if ($vote) {
            $choice = $this->guessUserChoice($choice, $votable->getUserChoice($user));
            $vote->setChoice($choice);

            if ($choice === VoteInterface::VOTE_NONE) {
                $votable->updateVoteCounts();
                $this->entityManager->remove($vote);
            }
        } else {
            $vote = $this->voteFactory->create($choice, $votable, $user);
            $this->entityManager->persist($vote);
        }

        $votable->updateVoteCounts();

        $this->entityManager->flush();

        return $vote;
    }

    private function guessUserChoice(int $choice, int $vote): int
    {
        if ($choice === VoteInterface::VOTE_NONE) {
            return $choice;
        }

        if ($vote === VoteInterface::VOTE_UP) {
            switch ($choice) {
                case VoteInterface::VOTE_UP:
                    return VoteInterface::VOTE_NONE;
                case VoteInterface::VOTE_DOWN:
                    return VoteInterface::VOTE_DOWN;
            }
        }

        if ($vote === VoteInterface::VOTE_DOWN) {
            switch ($choice) {
                case VoteInterface::VOTE_UP:
                    return VoteInterface::VOTE_UP;
                case VoteInterface::VOTE_DOWN:
                    return VoteInterface::VOTE_NONE;
            }
        }

        throw new \LogicException();
    }
}
