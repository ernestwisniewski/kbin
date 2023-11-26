<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\MarkNewComment;

use App\Entity\Entry;
use App\Entity\Post;
use App\Entity\User;
use App\Kbin\MarkNewComment\MessageBus\SubjectHasBeenSeenMessage;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class MarkNewCommentViewSubject
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function __invoke(User $user, Post|Entry $subject): void
    {
        if (!$user->markNewComments) {
            return;
        }

        $this->messageBus->dispatch(
            new SubjectHasBeenSeenMessage($user->getId(), $subject->getId(), \get_class($subject))
        );
    }
}
