<?php

declare(strict_types=1);

namespace App\Kbin\Vote;

use App\Entity\Contracts\VotableInterface;
use App\Entity\EntryComment;
use App\Entity\PostComment;
use App\Entity\User;
use App\Entity\Vote;
use App\Event\VoteEvent;
use App\Kbin\Vote\Factory\VoteFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

readonly class VoteUp
{
    public function __construct(
        private VoteFactory $voteFactory,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(VotableInterface $votable, User $user): Vote
    {
        if ($user->isBot) {
            throw new AccessDeniedHttpException('Bots are not allowed to vote on items!');
        }

        // @todo save activity pub object id
        $vote = $votable->getUserVote($user);

        if ($vote) {
            return $vote;
        }

        $vote = $this->voteFactory->create(1, $votable, $user);

        $votable->updateVoteCounts();

        $votable->lastActive = new \DateTime();

        if ($votable instanceof PostComment) {
            $votable->post->lastActive = new \DateTime();
        }

        if ($votable instanceof EntryComment) {
            $votable->entry->lastActive = new \DateTime();
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new VoteEvent($votable, $vote, false));

        return $vote;
    }
}
