<?php

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Post;
use App\Event\Post\PostEditedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class PostPin
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Post $post): Post
    {
        $post->sticky = !$post->sticky;

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostEditedEvent($post));

        return $post;
    }
}
