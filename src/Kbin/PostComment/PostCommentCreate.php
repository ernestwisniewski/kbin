<?php

declare(strict_types=1);

namespace App\Kbin\PostComment;

use App\DTO\PostCommentDto;
use App\Entity\PostComment;
use App\Entity\User;
use App\Event\PostComment\PostCommentCreatedEvent;
use App\Exception\UserBannedException;
use App\Factory\PostCommentFactory;
use App\Repository\ImageRepository;
use App\Service\MentionManager;
use App\Service\TagManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

readonly class PostCommentCreate
{
    public function __construct(
        private TagManager $tagManager,
        private MentionManager $mentionManager,
        private PostCommentFactory $postCommentFactory,
        private ImageRepository $imageRepository,
        private RateLimiterFactory $postCommentLimiter,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(PostCommentDto $dto, User $user, $rateLimit = true): PostComment
    {
        if ($rateLimit) {
            $limiter = $this->postCommentLimiter->create($dto->ip);
            if ($limiter && false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $comment = $this->postCommentFactory->createFromDto($dto, $user);

        if ($dto->post->magazine->isBanned($user)) {
            throw new UserBannedException();
        }

        $comment->magazine = $dto->post->magazine;
        $comment->lang = $dto->lang;
        $comment->isAdult = $dto->isAdult || $comment->magazine->isAdult;
        $comment->image = $dto->image ? $this->imageRepository->find($dto->image->id) : null;
        if ($comment->image && !$comment->image->altText) {
            $comment->image->altText = $dto->imageAlt;
        }
        $comment->tags = $dto->body ? $this->tagManager->extract($dto->body, $comment->magazine->name) : null;
        $comment->mentions = $dto->body
            ? array_merge($dto->mentions ?? [], $this->mentionManager->handleChain($comment))
            : $dto->mentions;
        $comment->visibility = $dto->getVisibility();
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

        $this->eventDispatcher->dispatch(new PostCommentCreatedEvent($comment));

        return $comment;
    }
}
