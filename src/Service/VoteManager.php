<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts\VotableInterface;
use App\Entity\EntryComment;
use App\Entity\PostComment;
use App\Entity\User;
use App\Entity\Vote;
use App\Event\VoteEvent;
use App\Factory\VoteFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class VoteManager
{
    public function __construct(
        private readonly VoteFactory $factory,
        private readonly RateLimiterFactory $voteLimiter,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function vote(int $choice, VotableInterface $votable, User $user, $rateLimit = true): Vote
    {
        if ($rateLimit) {
            $limiter = $this->voteLimiter->create($user->username);
            if (false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        if ($user->isBot) {
            throw new AccessDeniedHttpException('Bots are not allowed to vote on items!');
        }

        $vote = $votable->getUserVote($user);
        $votedAgain = false;

        if ($vote) {
            $votedAgain = true;
            $choice = $this->guessUserChoice($choice, $votable->getUserChoice($user));
            $vote->choice = $choice;
        } else {
            if (VotableInterface::VOTE_UP === $choice) {
                return $this->upvote($votable, $user);
            }

            $vote = $this->factory->create($choice, $votable, $user);
            $this->entityManager->persist($vote);
        }

        $votable->updateVoteCounts();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new VoteEvent($votable, $vote, $votedAgain));

        return $vote;
    }

    private function guessUserChoice(int $choice, int $vote): int
    {
        if (VotableInterface::VOTE_NONE === $choice) {
            return $choice;
        }

        if (VotableInterface::VOTE_UP === $vote) {
            return match ($choice) {
                VotableInterface::VOTE_UP => VotableInterface::VOTE_NONE,
                VotableInterface::VOTE_DOWN => VotableInterface::VOTE_DOWN,
                default => throw new \LogicException(),
            };
        }

        if (VotableInterface::VOTE_DOWN === $vote) {
            return match ($choice) {
                VotableInterface::VOTE_UP => VotableInterface::VOTE_UP,
                VotableInterface::VOTE_DOWN => VotableInterface::VOTE_NONE,
                default => throw new \LogicException(),
            };
        }

        return $choice;
    }

    public function upvote(VotableInterface $votable, User $user): Vote
    {
        if ($user->isBot) {
            throw new AccessDeniedHttpException('Bots are not allowed to vote on items!');
        }

        // @todo save activity pub object id
        $vote = $votable->getUserVote($user);

        if ($vote) {
            return $vote;
        }

        $vote = $this->factory->create(1, $votable, $user);

        $votable->updateVoteCounts();

        $votable->lastActive = new \DateTime();

        if ($votable instanceof PostComment) {
            $votable->post->lastActive = new \DateTime();
        }

        if ($votable instanceof EntryComment) {
            $votable->entry->lastActive = new \DateTime();
        }

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new VoteEvent($votable, $vote, false));

        return $vote;
    }

    public function removeVote(VotableInterface $votable, User $user): ?Vote
    {
        if ($user->isBot) {
            throw new AccessDeniedHttpException('Bots are not allowed to vote on items!');
        }

        // @todo save activity pub object id
        $vote = $votable->getUserVote($user);

        if (!$vote) {
            return null;
        }

        $vote->choice = VotableInterface::VOTE_NONE;

        $votable->updateVoteCounts();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new VoteEvent($votable, $vote, false));

        return $vote;
    }
}
