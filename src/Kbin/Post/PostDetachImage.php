<?php

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Post;
use App\Message\DeleteImageMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class PostDetachImage
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Post $post): void
    {
        $image = $post->image->filePath;

        $post->image = null;

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new DeleteImageMessage($image));
    }
}
