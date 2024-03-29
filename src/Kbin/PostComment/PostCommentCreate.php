<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\PostComment;

use App\Entity\PostComment;
use App\Entity\User;
use App\Kbin\PostComment\DTO\PostCommentDto;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentCreatedEvent;
use App\Kbin\PostComment\Factory\PostCommentFactory;
use App\Kbin\Tag\TagExtract;
use App\Repository\ImageRepository;
use App\Service\MentionManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

readonly class PostCommentCreate
{
    public function __construct(
        private TagExtract $tagExtract,
        private MentionManager $mentionManager,
        private PostCommentFactory $postCommentFactory,
        private ImageRepository $imageRepository,
        private RateLimiterFactory $postCommentLimiter,
        private RateLimiterFactory $spamProtectionLimiter,
        private EventDispatcherInterface $eventDispatcher,
        private AccessDecisionManagerInterface $accessDecisionManager,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(PostCommentDto $dto, User $user, $rateLimit = true): PostComment
    {
        if ($rateLimit) {
            $limiter = $this->postCommentLimiter->create($dto->ip);
            $spamProtection = $this->spamProtectionLimiter->create($dto->ip);
            if (false === $limiter->consume()->isAccepted() || false === $spamProtection->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $token = new UsernamePasswordToken($user, 'firewall', $user->getRoles());
        if (false === $this->accessDecisionManager->decide($token, ['create_content'], $dto->post->magazine)) {
            throw new AccessDeniedHttpException();
        }

        $comment = $this->postCommentFactory->createFromDto($dto, $user);

        $comment->magazine = $dto->post->magazine;
        $comment->lang = $dto->lang;
        $comment->isAdult = $dto->isAdult || $comment->magazine->isAdult;
        $comment->image = $dto->image ? $this->imageRepository->find($dto->image->id) : null;
        if ($comment->image && !$comment->image->altText) {
            $comment->image->altText = $dto->imageAlt;
        }
        $comment->tags = $dto->body ? ($this->tagExtract)($dto->body, $comment->magazine->name) : null;
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

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostCommentCreatedEvent($comment));

        return $comment;
    }
}
