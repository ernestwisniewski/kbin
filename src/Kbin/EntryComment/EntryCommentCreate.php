<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EntryComment;

use App\Entity\EntryComment;
use App\Entity\User;
use App\Kbin\EntryComment\DTO\EntryCommentDto;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentCreatedEvent;
use App\Kbin\EntryComment\Factory\EntryCommentFactory;
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

readonly class EntryCommentCreate
{
    public function __construct(
        private TagExtract $tagExtract,
        private MentionManager $mentionManager,
        private EntryCommentFactory $entryCommentFactory,
        private ImageRepository $imageRepository,
        private RateLimiterFactory $entryCommentLimiter,
        private RateLimiterFactory $spamProtectionLimiter,
        private EventDispatcherInterface $eventDispatcher,
        private AccessDecisionManagerInterface $accessDecisionManager,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(EntryCommentDto $dto, User $user, $rateLimit = true): EntryComment
    {
        if ($rateLimit) {
            $limiter = $this->entryCommentLimiter->create($dto->ip);
            $spamProtection = $this->spamProtectionLimiter->create($dto->ip);
            if (false === $limiter->consume()->isAccepted() || false === $spamProtection->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $token = new UsernamePasswordToken($user, 'firewall', $user->getRoles());
        if (false === $this->accessDecisionManager->decide($token, ['create_content'], $dto->entry->magazine)) {
            throw new AccessDeniedHttpException();
        }

        $comment = $this->entryCommentFactory->createFromDto($dto, $user);

        $comment->magazine = $dto->entry->magazine;
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
        $comment->visibility = $dto->visibility;
        $comment->apId = $dto->apId;
        $comment->magazine->lastActive = new \DateTime();
        $comment->user->lastActive = new \DateTime();
        $comment->lastActive = $dto->lastActive ?? $comment->lastActive;
        $comment->entry->lastActive = new \DateTime();
        $comment->createdAt = $dto->createdAt ?? $comment->createdAt;
        if (empty($comment->body) && null === $comment->image) {
            throw new \Exception('Comment body and image cannot be empty');
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryCommentCreatedEvent($comment));

        return $comment;
    }
}
