<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\MarkNewComment;

use App\Entity\Entry;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\ViewRepository;

readonly class MarkNewCommentLastSeen
{
    public function __construct(private ViewRepository $viewRepository)
    {
    }

    public function __invoke(User $user, Post|Entry $subject): ?\DateTime
    {
        if (!$user->markNewComments) {
            return null;
        }

        $subjectType = Entry::class === \get_class($subject) ? 'entry' : 'post';

        $entity = $this->viewRepository->findOneBy([
            'user' => $user,
            $subjectType => $subject,
        ]);

        if (!$entity) {
            return null;
        }

        return $entity->lastActive;
    }
}
