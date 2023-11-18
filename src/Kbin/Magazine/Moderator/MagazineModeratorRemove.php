<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine\Moderator;

use App\Entity\Moderator;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;

readonly class MagazineModeratorRemove
{
    public function __construct(
        private CacheInterface $cache,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Moderator $moderator): void
    {
        $user = $moderator->user;

        $this->entityManager->remove($moderator);
        $this->entityManager->flush();

        $this->clearCommentsCache($user);
    }

    private function clearCommentsCache(User $user): void
    {
        $this->cache->invalidateTags([
            'post_comments_user_'.$user->getId(),
            'entry_comments_user_'.$user->getId(),
        ]); // @todo move to event subscriber
    }
}
