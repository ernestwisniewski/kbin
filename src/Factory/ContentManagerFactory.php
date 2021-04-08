<?php declare(strict_types=1);

namespace App\Factory;

use App\Entity\Contracts\ContentInterface;
use App\Service\Contracts\ContentManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\EntryCommentManager;
use App\Service\PostCommentManager;
use App\Service\EntryManager;
use App\Service\PostManager;
use App\Entity\EntryComment;
use App\Entity\Entry;
use App\Entity\Post;
use LogicException;

class ContentManagerFactory
{
    public function __construct(
        private EntryManager $entryManager,
        private EntryCommentManager $entryCommentManager,
        private PostManager $postManager,
        private PostCommentManager $postCommentManager,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function createManager(ContentInterface $subject): ContentManager
    {
        return match ($this->entityManager->getClassMetadata(get_class($subject))->name) {
            Entry::class => $this->entryManager,
            EntryComment::class => $this->entryCommentManager,
            Post::class => $this->postManager,
            PostCommentManager::class => $this->postCommentManager,
            default => throw new LogicException(),
        };
    }
}
