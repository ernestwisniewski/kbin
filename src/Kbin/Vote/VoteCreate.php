<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Vote;

use App\Entity\Contracts\VotableInterface;
use App\Entity\User;
use App\Entity\Vote;
use App\Event\VoteEvent;
use App\Kbin\Vote\Factory\VoteFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

readonly class VoteCreate
{
    public function __construct(
        private VoteUp $voteUp,
        private VoteFactory $voteFactory,
        private RateLimiterFactory $voteLimiter,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(int $choice, VotableInterface $votable, User $user, $rateLimit = true): Vote
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
                return ($this->voteUp)($votable, $user);
            }

            $vote = $this->voteFactory->create($choice, $votable, $user);
            $this->entityManager->persist($vote);
        }

        $votable->updateVoteCounts();

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new VoteEvent($votable, $vote, $votedAgain));

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
}
