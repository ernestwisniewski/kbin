<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\PostComment;

use App\Entity\PostComment;
use App\Entity\User;
use App\Kbin\MessageBus\ImagePurgeMessage;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentBeforePurgeEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentPurgedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class PostCommentPurge
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(User $user, PostComment $comment): void
    {
        $this->eventDispatcher->dispatch(new PostCommentBeforePurgeEvent($comment, $user));

        $post = $comment->post;
        $user = $post->user;

        $image = $comment->image?->filePath;
        $comment->post->removeComment($comment);
        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new PostCommentPurgedEvent($post, $user));

        if ($image) {
            $this->messageBus->dispatch(new ImagePurgeMessage($image));
        }
    }
}
