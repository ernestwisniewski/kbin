<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\PostComment;

use App\Entity\PostComment;
use App\Kbin\MessageBus\ImagePurgeMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class PostCommentImageDetach
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(PostComment $comment): void
    {
        $image = $comment->image->filePath;

        $comment->image = null;

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new ImagePurgeMessage($image));
    }
}
