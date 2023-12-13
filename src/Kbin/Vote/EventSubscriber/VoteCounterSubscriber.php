<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Vote\EventSubscriber;

use App\Entity\Contracts\VotableInterface;
use App\Kbin\Vote\EventSubscriber\Event\VoteEvent;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class VoteCounterSubscriber
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VoteRepository $voteRepository,
    ) {
    }

    #[AsEventListener(event: VoteEvent::class, priority: -1)]
    public function onVote(VoteEvent $event): void
    {
        $event->votable->upVotes = $this->voteRepository->countBySubject(
            $event->votable,
            VotableInterface::VOTE_UP
        );
        $event->votable->downVotes = $this->voteRepository->countBySubject(
            $event->votable,
            VotableInterface::VOTE_DOWN
        );
        $event->votable->score = $event->votable->upVotes - $event->votable->downVotes;
        $event->votable->updateRanking();

        $this->entityManager->persist($event->votable);
        $this->entityManager->flush();
    }
}
