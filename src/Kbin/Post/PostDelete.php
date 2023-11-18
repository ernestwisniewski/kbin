<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Post;
use App\Entity\User;
use App\Event\Post\PostBeforeDeletedEvent;
use App\Event\Post\PostDeletedEvent;
use App\Kbin\Contract\DeleteContentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class PostDelete implements DeleteContentServiceInterface
{
    public function __construct(
        private PostPurge $postPurge,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user, ContentInterface|Post $subject): void
    {
        if ($user->apDomain && $user->apDomain !== parse_url($subject->apId, PHP_URL_HOST)) {
            return;
        }

        if ($subject->isAuthor($user) && $subject->comments->isEmpty()) {
            ($this->postPurge)($user, $subject);

            return;
        }

        $this->isTrashed($user, $subject) ? $subject->trash() : $subject->softDelete();

        $this->eventDispatcher->dispatch(new PostBeforeDeletedEvent($subject, $user));

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostDeletedEvent($subject, $user));
    }

    private function isTrashed(User $user, Post $post): bool
    {
        return !$post->isAuthor($user);
    }
}
