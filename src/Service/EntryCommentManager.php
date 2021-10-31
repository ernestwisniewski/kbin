<?php declare(strict_types = 1);

namespace App\Service;

use App\DTO\EntryCommentDto;
use App\Entity\EntryComment;
use App\Entity\User;
use App\Event\EntryComment\EntryCommentBeforePurgeEvent;
use App\Event\EntryComment\EntryCommentCreatedEvent;
use App\Event\EntryComment\EntryCommentDeletedEvent;
use App\Event\EntryComment\EntryCommentPurgedEvent;
use App\Event\EntryComment\EntryCommentUpdatedEvent;
use App\Factory\EntryCommentFactory;
use App\Service\Contracts\ContentManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Webmozart\Assert\Assert;

class EntryCommentManager implements ContentManagerInterface
{
    public function __construct(
        private EntryCommentFactory $factory,
        private EventDispatcherInterface $dispatcher,
        private RateLimiterFactory $entryCommentLimiter,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(EntryCommentDto $dto, User $user): EntryComment
    {
        $limiter = $this->entryCommentLimiter->create($dto->ip);
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $comment = $this->factory->createFromDto($dto, $user);

        $comment->entry->addComment($comment);
        $comment->magazine = $dto->entry->magazine;

        if ($dto->image) {
            $comment->image = $dto->image;
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryCommentCreatedEvent($comment));

        return $comment;
    }

    public function edit(EntryComment $comment, EntryCommentDto $dto): EntryComment
    {
        Assert::same($comment->entry->getId(), $dto->entry->getId());

        $comment->body = $dto->body;
        if ($dto->image) {
            $comment->image = $dto->image;
        }

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryCommentUpdatedEvent($comment));

        return $comment;
    }

    public function delete(User $user, EntryComment $comment): void
    {
        if ($comment->isAuthor($user) && $comment->children->isEmpty()) {
            $this->purge($user, $comment);

            return;
        }

        $this->isTrashed($user, $comment) ? $comment->trash() : $comment->softDelete();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryCommentDeletedEvent($comment, $user));
    }

    public function purge(User $user, EntryComment $comment): void
    {
        $this->dispatcher->dispatch(new EntryCommentBeforePurgeEvent($comment));

        $magazine = $comment->entry->magazine;
        $comment->entry->removeComment($comment);

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryCommentPurgedEvent($magazine));
    }

    private function isTrashed(User $user, EntryComment $comment): bool
    {
        return !$comment->isAuthor($user);
    }

    public function createDto(EntryComment $comment): EntryCommentDto
    {
        return $this->factory->createDto($comment);
    }
}
