<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts\VoteInterface;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use App\Entity\Vote;
use App\Event\VoteEvent;
use App\Factory\VoteFactory;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class VoteManager
{
    public function __construct(
        private VoteFactory $factory,
        private RateLimiterFactory $voteLimiter,
        private EventDispatcherInterface $dispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function vote(int $choice, VoteInterface $votable, User $user, $limiter = true): Vote
    {
        if ($limiter) {
            $limiter = $this->voteLimiter->create($user->username);
            if (false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $vote       = $votable->getUserVote($user);
        $votedAgain = false;

        if ($vote) {
            $votedAgain   = true;
            $choice       = $this->guessUserChoice($choice, $votable->getUserChoice($user));
            $vote->choice = $choice;
        } else {
            if ($votable instanceof Post || $votable instanceof PostComment) {
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
        if ($choice === VoteInterface::VOTE_NONE) {
            return $choice;
        }

        if ($vote === VoteInterface::VOTE_UP) {
            return match ($choice) {
                VoteInterface::VOTE_UP => VoteInterface::VOTE_NONE,
                VoteInterface::VOTE_DOWN => VoteInterface::VOTE_DOWN,
                default => throw new LogicException(),
            };
        }

        if ($vote === VoteInterface::VOTE_DOWN) {
            return match ($choice) {
                VoteInterface::VOTE_UP => VoteInterface::VOTE_UP,
                VoteInterface::VOTE_DOWN => VoteInterface::VOTE_NONE,
                default => throw new LogicException(),
            };
        }

        return $choice;
    }

    public function upvote(VoteInterface $votable, User $user): Vote
    {
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

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new VoteEvent($votable, $vote, false));

        return $vote;
    }
}
