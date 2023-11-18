<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\PostComment;

use App\Entity\PostComment;
use App\Entity\User;
use App\Event\PostComment\PostCommentBeforeDeletedEvent;
use App\Event\PostComment\PostCommentDeletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class PostCommentTrash
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(User $user, PostComment $comment): void
    {
        $comment->trash();

        $this->eventDispatcher->dispatch(new PostCommentBeforeDeletedEvent($comment, $user));

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostCommentDeletedEvent($comment, $user));
    }
}
