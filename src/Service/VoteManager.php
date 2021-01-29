<?php declare(strict_types=1);

namespace App\Service;

use App\Event\SubjectVotedEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Contracts\Votable;
use App\Factory\VoteFactory;
use App\Entity\Vote;
use App\Entity\User;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
        $vote = $votable->getUserVote($user);

        if ($vote) {
            $choice = $this->guessUserChoice($choice, $votable->getUserChoice($user));
            $vote->setChoice($choice);

            if ($choice === Votable::VOTE_NONE) {
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
        if ($choice === Votable::VOTE_NONE) {
            return $choice;
        }

        if ($vote === Votable::VOTE_UP) {
            switch ($choice) {
                case Votable::VOTE_UP:
                    return Votable::VOTE_NONE;
                case Votable::VOTE_DOWN:
                    return Votable::VOTE_DOWN;
            }
        }

        if ($vote === Votable::VOTE_DOWN) {
            switch ($choice) {
                case Votable::VOTE_UP:
                    return Votable::VOTE_UP;
                case Votable::VOTE_DOWN:
                    return Votable::VOTE_NONE;
            }
        }

        throw new \LogicException();
    }
}
