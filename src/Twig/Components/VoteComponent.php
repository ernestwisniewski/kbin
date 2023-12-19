<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Contracts\VotableInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\User;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('vote')]
final class VoteComponent
{
    public VotableInterface $subject;
    public string $formDest;
    public bool $showDownvote = true;

    public function __construct(private readonly CacheInterface $cache)
    {
    }

    #[PostMount]
    public function postMount(array $attr): array
    {
        $this->formDest = $this->getVotableCacheKey($this->subject);

        return $attr;
    }

    public function isFavourite(VotableInterface $votable, User $user): bool
    {
        return $this->cache->get(
            "favourite_{$votable->getId()}_{$user->getId()}",
            function (ItemInterface $item) use ($votable, $user): bool {
                $item->expiresAfter(1800);
                $item->tag([$this->getVotableCacheKey($votable).'_'.$votable->getId()]);

                return $votable->isFavored($user);
            }
        );
    }

    public function userChoice(VotableInterface $votable, User $user): ?int
    {
        return $this->cache->get(
            "vote_{$votable->getId()}_{$user->getId()}",
            function (ItemInterface $item) use ($votable, $user): int {
                $item->expiresAfter(1800);
                $item->tag([$this->getVotableCacheKey($votable).'_'.$votable->getId()]);

                return $votable->getUserChoice($user);
            }
        );
    }

    private function getVotableCacheKey(VotableInterface $subject): string
    {
        return match (true) {
            $this->subject instanceof Entry => 'entry',
            $this->subject instanceof EntryComment => 'entry_comment',
            $this->subject instanceof Post => 'post',
            $this->subject instanceof PostComment => 'post_comment',
            default => throw new \LogicException(),
        };
    }
}
