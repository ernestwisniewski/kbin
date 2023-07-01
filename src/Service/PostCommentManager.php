<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\PostCommentDto;
use App\Entity\Contracts\VisibilityInterface;
use App\Entity\PostComment;
use App\Entity\User;
use App\Event\PostComment\PostCommentBeforeDeletedEvent;
use App\Event\PostComment\PostCommentBeforePurgeEvent;
use App\Event\PostComment\PostCommentCreatedEvent;
use App\Event\PostComment\PostCommentDeletedEvent;
use App\Event\PostComment\PostCommentEditedEvent;
use App\Event\PostComment\PostCommentPurgedEvent;
use App\Event\PostComment\PostCommentRestoredEvent;
use App\Exception\UserBannedException;
use App\Factory\PostCommentFactory;
use App\Message\DeleteImageMessage;
use App\Service\Contracts\ContentManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Webmozart\Assert\Assert;

class PostCommentManager implements ContentManagerInterface
{
    public function __construct(
        private readonly TagManager $tagManager,
        private readonly MentionManager $mentionManager,
        private readonly PostCommentFactory $factory,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly RateLimiterFactory $postCommentLimiter,
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function create(PostCommentDto $dto, User $user, $limiter = true): PostComment
    {
        if ($limiter) {
            $limiter = $this->postCommentLimiter->create($dto->ip);
            if ($limiter && false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $comment = $this->factory->createFromDto($dto, $user);

        if ($dto->post->magazine->isBanned($user)) {
            throw new UserBannedException();
        }

        $comment->magazine = $dto->post->magazine;
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

        $comment->post->addComment($comment);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCommentCreatedEvent($comment));

        return $comment;
    }

    public function edit(PostComment $comment, PostCommentDto $dto): PostComment
    {
        Assert::same($comment->post->getId(), $dto->post->getId());

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

        $this->dispatcher->dispatch(new PostCommentEditedEvent($comment));

        return $comment;
    }

    public function delete(User $user, PostComment $comment): void
    {
        if ($user->apId) {
            return;
        }

        if ($comment->isAuthor($user) && $comment->children->isEmpty()) {
            $this->purge($comment);

            return;
        }

        $this->isTrashed($user, $comment) ? $comment->trash() : $comment->softDelete();

        $this->dispatcher->dispatch(new PostCommentBeforeDeletedEvent($comment, $user));

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCommentDeletedEvent($comment, $user));
    }

    public function purge(PostComment $comment): void
    {
        $this->dispatcher->dispatch(new PostCommentBeforePurgeEvent($comment));

        $magazine = $comment->post->magazine;
        $image = $comment->image?->filePath;
        $comment->post->removeComment($comment);

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCommentPurgedEvent($magazine));

        if ($image) {
            $this->bus->dispatch(new DeleteImageMessage($image));
        }
    }

    private function isTrashed(User $user, PostComment $comment): bool
    {
        return !$comment->isAuthor($user);
    }

    public function restore(User $user, PostComment $comment): void
    {
        if (VisibilityInterface::VISIBILITY_TRASHED !== $comment->visibility) {
            throw new \Exception('Invalid visibility');
        }

        $comment->visibility = VisibilityInterface::VISIBILITY_VISIBLE;

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PostCommentRestoredEvent($comment, $user));
    }

    public function createDto(PostComment $comment): PostCommentDto
    {
        return $this->factory->createDto($comment);
    }

    public function detachImage(PostComment $comment): void
    {
        $image = $comment->image->filePath;

        $comment->image = null;

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->bus->dispatch(new DeleteImageMessage($image));
    }
}
