<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Post;
use App\Message\DeleteImageMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class PostImageDetach
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
