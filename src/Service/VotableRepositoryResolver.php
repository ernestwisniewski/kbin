<?php declare(strict_types = 1);

namespace App\Service;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Repository\EntryCommentRepository;
use App\Repository\EntryRepository;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use LogicException;

class VotableRepositoryResolver
{
    public function __construct(
        private EntryRepository $entryRepository,
        private EntryCommentRepository $entryCommentRepository,
        private PostRepository $postRepository,
        private PostCommentRepository $postCommentRepository
    ) {
    }

    public function resolve(string $entityClass)
    {
        return match ($entityClass) {
            Entry::class => $this->entryRepository,
            EntryComment::class => $this->entryCommentRepository,
            Post::class => $this->postRepository,
            PostComment::class => $this->postCommentRepository,
            default => throw new LogicException(),
        };
    }

}
