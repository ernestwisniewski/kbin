<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service;

use App\Repository\EntryCommentRepository;
use App\Repository\EntryRepository;
use App\Repository\MagazineRepository;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\VoteRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class InstanceStatsManager
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MagazineRepository $magazineRepository,
        private readonly EntryRepository $entryRepository,
        private readonly EntryCommentRepository $entryCommentRepository,
        private readonly PostRepository $postRepository,
        private readonly PostCommentRepository $postCommentRepository,
        private readonly VoteRepository $voteRepository,
        private readonly CacheInterface $cache
    ) {
    }

    public function count(string $period = null, bool $withFederated = false)
    {
        $periodDate = $period ? \DateTimeImmutable::createFromMutable(new \DateTime($period)) : null;

        return $this->cache->get('instance_stats', function (ItemInterface $item) use ($periodDate, $withFederated) {
            $item->expiresAfter(0);

            $criteria = Criteria::create();

            if ($periodDate) {
                $criteria->where(
                    Criteria::expr()
                        ->gt('createdAt', $periodDate)
                );
            }

            if (!$withFederated) {
                if ($periodDate) {
                    $criteria->andWhere(
                        Criteria::expr()->eq('apId', null)
                    );
                } else {
                    $criteria->where(
                        Criteria::expr()->eq('apId', null)
                    );
                }
            }

            return [
                'users' => $this->userRepository->matching($criteria)->count(),
                'magazines' => $this->magazineRepository->matching($criteria)->count(),
                'entries' => $this->entryRepository->matching($criteria)->count(),
                'comments' => $this->entryCommentRepository->matching($criteria)->count(),
                'posts' => $this->postRepository->matching($criteria)->count() + $this->postCommentRepository->matching(
                    $criteria
                )->count(),
                'votes' => $this->voteRepository->count($periodDate, $withFederated),
            ];
        });
    }
}
