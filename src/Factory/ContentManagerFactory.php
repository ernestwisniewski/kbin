<?php declare(strict_types=1);

namespace App\Factory;

use App\Entity\Contracts\ContentInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\DTO\EntryCommentDto;
use App\Entity\Post;
use App\Entity\User;
use App\Service\EntryCommentManager;
use App\Service\EntryManager;
use App\Service\PostCommentManager;
use App\Service\PostManager;
use Doctrine\ORM\EntityManagerInterface;

class ContentManagerFactory
{
    private EntryManager $entryManager;
    private EntryCommentManager $entryCommentManager;
    private PostManager $postManager;
    private PostCommentManager $postCommentManager;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntryManager $entryManager,
        EntryCommentManager $entryCommentManager,
        PostManager $postManager,
        PostCommentManager $postCommentManager,
        EntityManagerInterface $entityManager
    ) {

        $this->entryManager        = $entryManager;
        $this->entryCommentManager = $entryCommentManager;
        $this->postManager         = $postManager;
        $this->postCommentManager  = $postCommentManager;
        $this->entityManager       = $entityManager;
    }

    public function createManager(ContentInterface $subject)
    {
        switch ($this->entityManager->getClassMetadata(get_class($subject))->getName()) {
            case Entry::class:
                return $this->entryManager;
            case EntryComment::class:
                return $this->entryCommentManager;
            case Post::class:
                return $this->postManager;
            case PostCommentManager::class:
                return $this->postCommentManager;
        }

        throw new \LogicException();
    }
}
