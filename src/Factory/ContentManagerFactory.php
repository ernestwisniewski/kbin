<?php declare(strict_types=1);

namespace App\Factory;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\DTO\EntryCommentDto;
use App\Entity\Post;
use App\Entity\User;
use App\Service\Contracts\ContentManager;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use Doctrine\ORM\EntityManagerInterface;

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
        return match ($this->entityManager->getClassMetadata(get_class($subject))->getName()) {
            Entry::class => $this->entryManager,
            EntryComment::class => $this->entryCommentManager,
            Post::class => $this->postManager,
            PostCommentManager::class => $this->postCommentManager,
            default => throw new \LogicException(),
        };
    }
}
