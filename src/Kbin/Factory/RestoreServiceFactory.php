<?php

declare(strict_types=1);

namespace App\Kbin\Factory;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Kbin\Contracts\RestoreServiceInterface;
use App\Kbin\Entry\EntryRestore;
use App\Kbin\EntryComment\EntryCommentRestore;
use App\Kbin\Post\PostRestore;
use App\Kbin\PostComment\PostCommentRestore;
use Doctrine\ORM\EntityManagerInterface;

readonly class RestoreServiceFactory
{
    public function __construct(
        private EntryRestore $entryRestore,
        private EntryCommentRestore $entryCommentRestore,
        private PostRestore $postRestore,
        private PostCommentRestore $postCommentRestore,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(ContentInterface $subject): RestoreServiceInterface
    {
        return match ($this->entityManager->getClassMetadata(\get_class($subject))->name) {
            Entry::class => $this->entryRestore,
            EntryComment::class => $this->entryCommentRestore,
            Post::class => $this->postRestore,
            PostComment::class => $this->postCommentRestore,
            default => throw new \LogicException(),
        };
    }
}
