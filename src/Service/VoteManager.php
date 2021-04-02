<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\PostComment;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Contracts\VoteInterface;
use App\Factory\VoteFactory;
use App\Entity\Vote;
use App\Entity\User;

class VoteManager
{
    public function __construct(
        private VoteFactory $voteFactory,
        private EntityManagerInterface $entityManager
    ) {
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
            return match ($choice) {
                VoteInterface::VOTE_UP => VoteInterface::VOTE_NONE,
                VoteInterface::VOTE_DOWN => VoteInterface::VOTE_DOWN,
                default => throw new \LogicException(),
            };
        }

        if ($vote === VoteInterface::VOTE_DOWN) {
            return match ($choice) {
                VoteInterface::VOTE_UP => VoteInterface::VOTE_UP,
                VoteInterface::VOTE_DOWN => VoteInterface::VOTE_NONE,
                default => throw new \LogicException(),
            };
        }
    }
}
