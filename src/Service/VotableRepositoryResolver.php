<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Repository\EntryCommentRepository;
use App\Repository\EntryRepository;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;

class VotableRepositoryResolver
{
    public function __construct(
        private readonly EntryRepository $entryRepository,
        private readonly EntryCommentRepository $entryCommentRepository,
        private readonly PostRepository $postRepository,
        private readonly PostCommentRepository $postCommentRepository
    ) {
    }

    public function resolve(string $entityClass)
    {
        return match ($entityClass) {
            Entry::class => $this->entryRepository,
            EntryComment::class => $this->entryCommentRepository,
            Post::class => $this->postRepository,
            PostComment::class => $this->postCommentRepository,
            default => throw new \LogicException(),
        };
    }
}
