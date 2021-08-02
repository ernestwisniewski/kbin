<?php declare(strict_types=1);

namespace App\Factory;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Service\Contracts\ContentManagerInterface;
use App\Service\EntryCommentManagerInterface;
use App\Service\EntryManagerInterface;
use App\Service\PostCommentManagerInterface;
use App\Service\PostManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

class ContentManagerFactory
{
    public function __construct(
        private EntryManagerInterface $entryManager,
        private EntryCommentManagerInterface $entryCommentManager,
        private PostManagerInterface $postManager,
        private PostCommentManagerInterface $postCommentManager,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function createManager(ContentInterface $subject): ContentManagerInterface
    {
        return match ($this->entityManager->getClassMetadata(get_class($subject))->name) {
            Entry::class => $this->entryManager,
            EntryComment::class => $this->entryCommentManager,
            Post::class => $this->postManager,
            PostCommentManagerInterface::class => $this->postCommentManager,
            default => throw new LogicException(),
        };
    }
}
