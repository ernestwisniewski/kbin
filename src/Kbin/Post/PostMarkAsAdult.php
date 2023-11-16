<?php

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Post;
use App\Event\Post\PostEditedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class PostMarkAsAdult
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Post $post, bool $marked = true): void
    {
        $post->isAdult = $marked;

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostEditedEvent($post));
    }
}
