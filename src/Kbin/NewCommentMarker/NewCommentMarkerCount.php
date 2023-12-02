<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\NewCommentMarker;

use App\Entity\Entry;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\ViewRepository;

readonly class NewCommentMarkerCount
{
    public function __construct(private ViewRepository $viewRepository)
    {
    }

    public function __invoke(User $user, Post|Entry $subject): int
    {
        if (!$user->markNewComments) {
            return 0;
        }

        $subjectType = Entry::class === \get_class($subject) ? 'entry' : 'post';

        $entity = $this->viewRepository->findOneBy([
            'user' => $user,
            $subjectType => $subject,
        ]);

        if (!$entity) {
            return 0;
        }

        return $subject->countCommentsNewestThan($entity->lastActive, $user);
    }
}
