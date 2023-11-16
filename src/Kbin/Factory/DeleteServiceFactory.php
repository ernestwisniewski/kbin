<?php

declare(strict_types=1);

namespace App\Kbin\Factory;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\Post;
use App\Kbin\Contracts\DeleteServiceInterface;
use App\Kbin\Entry\EntryDelete;
use App\Kbin\EntryComment\EntryCommentDelete;
use App\Kbin\Post\PostDelete;
use App\Kbin\PostComment\PostCommentDelete;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\EntryComment;

readonly class DeleteServiceFactory
{
    public function __construct(
        private EntryDelete $entryDelete,
        private EntryCommentDelete $entryCommentDelete,
        private PostDelete $postDelete,
        private PostCommentDelete $postCommentDelete,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(ContentInterface $subject): DeleteServiceInterface
    {
        return match ($this->entityManager->getClassMetadata(\get_class($subject))->name) {
            Entry::class => $this->entryDelete,
            EntryComment::class => $this->entryCommentDelete,
            Post::class => $this->postDelete,
            PostCommentDelete::class => $this->postCommentDelete,
            default => throw new \LogicException(),
        };
    }
}
