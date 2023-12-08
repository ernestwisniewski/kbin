<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Vote;

use App\Entity\Contracts\VotableInterface;
use App\Entity\User;
use App\Entity\Vote;
use App\Kbin\Vote\EventSubscriber\Event\VoteEvent;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

readonly class VoteRemove
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(VotableInterface $votable, User $user): ?Vote
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

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new VoteEvent($votable, $vote, false));

        return $vote;
    }
}
