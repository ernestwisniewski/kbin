<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EntryComment;

use App\Entity\EntryComment;
use App\Entity\User;
use App\Kbin\EntryComment\EventSubscriber\EntryComment\EntryCommentBeforeDeletedEvent;
use App\Kbin\EntryComment\EventSubscriber\EntryComment\EntryCommentDeletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class EntryCommentTrash
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user, EntryComment $comment): void
    {
        $comment->trash();

        $this->eventDispatcher->dispatch(new EntryCommentBeforeDeletedEvent($comment, $user));

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new EntryCommentDeletedEvent($comment, $user));
    }
}
