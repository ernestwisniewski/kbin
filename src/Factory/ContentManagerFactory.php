<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Service\Contracts\ContentManagerInterface;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use Doctrine\ORM\EntityManagerInterface;

class ContentManagerFactory
{
    public function __construct(
        private readonly EntryManager $entryManager,
        private readonly EntryCommentManager $entryCommentManager,
        private readonly PostManager $postManager,
        private readonly PostCommentManager $postCommentManager,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function createManager(ContentInterface $subject): ContentManagerInterface
    {
        return match ($this->entityManager->getClassMetadata(\get_class($subject))->name) {
            Entry::class => $this->entryManager,
            EntryComment::class => $this->entryCommentManager,
            Post::class => $this->postManager,
            PostCommentManager::class => $this->postCommentManager,
            default => throw new \LogicException(),
        };
    }
}
