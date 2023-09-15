<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Factory\EntryCommentFactory;
use App\Factory\EntryFactory;
use App\Factory\MagazineFactory;
use App\Factory\PostCommentFactory;
use App\Factory\PostFactory;
use Doctrine\ORM\EntityManagerInterface;

class FactoryResolver
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntryFactory $entryFactory,
        private readonly EntryCommentFactory $entryCommentFactory,
        private readonly PostFactory $postFactory,
        private readonly PostCommentFactory $postCommentFactory,
        private readonly MagazineFactory $magazineFactory
    ) {
    }

    public function resolve($subject)
    {
        return match ($this->entityManager->getClassMetadata(\get_class($subject))->name) {
            Entry::class => $this->entryFactory,
            EntryComment::class => $this->entryCommentFactory,
            Post::class => $this->postFactory,
            PostComment::class => $this->postCommentFactory,
            Magazine::class => $this->magazineFactory,
            default => throw new \LogicException(),
        };
    }
}
