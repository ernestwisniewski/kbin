<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\EntryCommentDto;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\EntryComment;
use App\Entity\User;
use App\Event\EntryComment\EntryCommentBeforePurgeEvent;
use App\Event\EntryComment\EntryCommentCreatedEvent;
use App\Event\EntryComment\EntryCommentDeletedEvent;
use App\Event\EntryComment\EntryCommentEditedEvent;
use App\Event\EntryComment\EntryCommentPurgedEvent;
use App\Event\EntryComment\EntryCommentRestoredEvent;
use App\Factory\EntryCommentFactory;
use App\Message\DeleteImageMessage;
use App\Service\Contracts\ContentManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Webmozart\Assert\Assert;

class EntryCommentManager implements ContentManagerInterface
{
    public function __construct(
        private TagManager $tagManager,
        private EntryCommentFactory $factory,
        private RateLimiterFactory $entryCommentLimiter,
        private EventDispatcherInterface $dispatcher,
        private MessageBusInterface $bus,
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

        $comment->magazine   = $dto->entry->magazine;
        $comment->image      = $dto->image;
        $comment->tags       = $this->tagManager->extract($comment->body);
        $comment->lastActive = new \DateTime();
        $comment->entry->addComment($comment);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryCommentCreatedEvent($comment));

        return $comment;
    }

    public function edit(EntryComment $comment, EntryCommentDto $dto): EntryComment
    {
        Assert::same($comment->entry->getId(), $dto->entry->getId());

        $comment->body  = $dto->body;
        $oldImage       = $comment->image;
        $comment->image = $dto->image;
        $comment->tags  = $this->tagManager->extract($comment->body);

        $this->entityManager->flush();

        if ($oldImage && $dto->image !== $oldImage) {
            $this->bus->dispatch(new DeleteImageMessage($oldImage->filePath));
        }

        $this->dispatcher->dispatch(new EntryCommentEditedEvent($comment));

        return $comment;
    }

    public function delete(User $user, EntryComment $comment): void
    {
        if ($comment->isAuthor($user) && $comment->children->isEmpty()) {
            $this->purge($comment);

            return;
        }

        $this->isTrashed($user, $comment) ? $comment->trash() : $comment->softDelete();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryCommentDeletedEvent($comment, $user));
    }

    public function purge(EntryComment $comment): void
    {
        $this->dispatcher->dispatch(new EntryCommentBeforePurgeEvent($comment));

        $magazine = $comment->entry->magazine;
        $image    = $comment->image?->filePath;
        $comment->entry->removeComment($comment);

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        if ($image) {
            $this->bus->dispatch(new DeleteImageMessage($image));
        }

        $this->dispatcher->dispatch(new EntryCommentPurgedEvent($magazine));
    }

    private function isTrashed(User $user, EntryComment $comment): bool
    {
        return !$comment->isAuthor($user);
    }

    public function restore(User $user, EntryComment $comment): void
    {
        if ($comment->visibility !== VisibilityInterface::VISIBILITY_TRASHED) {
            throw new \Exception('Invalid visibility');
        }

        $comment->visibility = VisibilityInterface::VISIBILITY_VISIBLE;

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryCommentRestoredEvent($comment, $user));
    }

    public function createDto(EntryComment $comment): EntryCommentDto
    {
        return $this->factory->createDto($comment);
    }

    public function detachImage(EntryComment $comment): void
    {
        $image = $comment->image->filePath;

        $comment->image = null;

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->bus->dispatch(new DeleteImageMessage($image));
    }
}
