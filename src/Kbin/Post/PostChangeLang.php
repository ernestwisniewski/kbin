<?php

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Post;
use App\Event\Post\PostEditedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class PostChangeLang
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Post $post, string $lang = 'en'): void
    {
        $post->lang = $lang;

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostEditedEvent($post));
    }
}
