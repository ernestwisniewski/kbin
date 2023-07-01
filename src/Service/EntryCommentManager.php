<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\EntryCommentDto;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\EntryComment;
use App\Entity\User;
use App\Event\EntryComment\EntryCommentBeforeDeletedEvent;
use App\Event\EntryComment\EntryCommentBeforePurgeEvent;
use App\Event\EntryComment\EntryCommentCreatedEvent;
use App\Event\EntryComment\EntryCommentDeletedEvent;
use App\Event\EntryComment\EntryCommentEditedEvent;
use App\Event\EntryComment\EntryCommentPurgedEvent;
use App\Event\EntryComment\EntryCommentRestoredEvent;
use App\Exception\UserBannedException;
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
        private readonly TagManager $tagManager,
        private readonly MentionManager $mentionManager,
        private readonly EntryCommentFactory $factory,
        private readonly RateLimiterFactory $entryCommentLimiter,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function create(EntryCommentDto $dto, User $user, $limiter = true): EntryComment
    {
        if ($limiter) {
            $limiter = $this->entryCommentLimiter->create($dto->ip);
            if ($limiter && false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $comment = $this->factory->createFromDto($dto, $user);

        if ($dto->entry->magazine->isBanned($user)) {
            throw new UserBannedException();
        }

        $comment->magazine = $dto->entry->magazine;
        $comment->lang = $dto->lang;
        $comment->isAdult = $dto->isAdult || $comment->magazine->isAdult;
        $comment->image = $dto->image;
        if ($comment->image && !$comment->image->altText) {
            $comment->image->altText = $dto->imageAlt;
        }
        $comment->tags = $dto->body ? $this->tagManager->extract($dto->body, $comment->magazine->name) : null;
        $comment->mentions = $dto->body
            ? array_merge($dto->mentions ?? [], $this->mentionManager->handleChain($comment))
            : $dto->mentions;
        $comment->visibility = $dto->visibility;
        $comment->apId = $dto->apId;
        $comment->magazine->lastActive = new \DateTime();
        $comment->user->lastActive = new \DateTime();
        $comment->lastActive = $dto->lastActive ?? $comment->lastActive;
        $comment->createdAt = $dto->createdAt ?? $comment->createdAt;
        if (empty($comment->body) && null === $comment->image) {
            throw new \Exception('Comment body and image cannot be empty');
        }

        $comment->entry->addComment($comment);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryCommentCreatedEvent($comment));

        return $comment;
    }

    public function edit(EntryComment $comment, EntryCommentDto $dto): EntryComment
    {
        Assert::same($comment->entry->getId(), $dto->entry->getId());

        $comment->body = $dto->body;
        $comment->lang = $dto->lang;
        $comment->isAdult = $dto->isAdult || $comment->magazine->isAdult;
        $oldImage = $comment->image;
        if ($dto->image) {
            $comment->image = $dto->image;
        }
        $comment->tags = $dto->body ? $this->tagManager->extract($dto->body, $comment->magazine->name) : null;
        $comment->mentions = $dto->body
            ? array_merge($dto->mentions ?? [], $this->mentionManager->handleChain($comment))
            : $dto->mentions;
        $comment->visibility = $dto->visibility;
        $comment->editedAt = new \DateTimeImmutable('@'.time());
        if (empty($comment->body) && null === $comment->image) {
            throw new \Exception('Comment body and image cannot be empty');
        }

        $this->entityManager->flush();

        if ($oldImage && $comment->image !== $oldImage) {
            $this->bus->dispatch(new DeleteImageMessage($oldImage->filePath));
        }

        $this->dispatcher->dispatch(new EntryCommentEditedEvent($comment));

        return $comment;
    }

    public function delete(User $user, EntryComment $comment): void
    {
        if ($user->apId) {
            return;
        }

        if ($comment->isAuthor($user) && $comment->children->isEmpty()) {
            $this->purge($comment);

            return;
        }

        $this->isTrashed($user, $comment) ? $comment->trash() : $comment->softDelete();

        $this->dispatcher->dispatch(new EntryCommentBeforeDeletedEvent($comment, $user));

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EntryCommentDeletedEvent($comment, $user));
    }

    public function purge(EntryComment $comment): void
    {
        $this->dispatcher->dispatch(new EntryCommentBeforePurgeEvent($comment));

        $magazine = $comment->entry->magazine;
        $image = $comment->image?->filePath;
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
        if (VisibilityInterface::VISIBILITY_TRASHED !== $comment->visibility) {
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
