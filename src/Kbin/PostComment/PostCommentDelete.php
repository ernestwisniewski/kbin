<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\PostComment;

use App\Entity\Contracts\ContentInterface;
use App\Entity\PostComment;
use App\Entity\User;
use App\Event\PostComment\PostCommentBeforeDeletedEvent;
use App\Event\PostComment\PostCommentDeletedEvent;
use App\Kbin\Contract\DeleteContentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class PostCommentDelete implements DeleteContentServiceInterface
{
    public function __construct(
        private PostCommentPurge $postCommentPurge,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(User $user, ContentInterface|PostComment $subject): void
    {
        if ($user->apDomain && $user->apDomain !== parse_url($subject->apId, PHP_URL_HOST)) {
            return;
        }

        if ($subject->isAuthor($user) && $subject->children->isEmpty()) {
            ($this->postCommentPurge)($user, $subject);

            return;
        }

        $this->isTrashed($user, $subject) ? $subject->trash() : $subject->softDelete();

        $this->eventDispatcher->dispatch(new PostCommentBeforeDeletedEvent($subject, $user));

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostCommentDeletedEvent($subject, $user));
    }

    private function isTrashed(User $user, PostComment $comment): bool
    {
        return !$comment->isAuthor($user);
    }
}
