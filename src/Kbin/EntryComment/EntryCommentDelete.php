<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EntryComment;

use App\Entity\Contracts\ContentInterface;
use App\Entity\EntryComment;
use App\Entity\User;
use App\Kbin\Contract\DeleteContentServiceInterface;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentBeforeDeletedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentDeletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class EntryCommentDelete implements DeleteContentServiceInterface
{
    public function __construct(
        private EntryCommentPurge $entryCommentPurge,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user, ContentInterface|EntryComment $subject): void
    {
        if ($user->apDomain && $user->apDomain !== parse_url($subject->apId, PHP_URL_HOST)) {
            return;
        }

        if ($subject->isAuthor($user) && $subject->children->isEmpty()) {
            ($this->entryCommentPurge)($user, $subject);

            return;
        }

        $this->isTrashed($user, $subject) ? $subject->trash() : $subject->softDelete();

        $this->eventDispatcher->dispatch(new EntryCommentBeforeDeletedEvent($subject, $user));

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryCommentDeletedEvent($subject, $user));
    }

    private function isTrashed(User $user, EntryComment $comment): bool
    {
        return !$comment->isAuthor($user);
    }
}
