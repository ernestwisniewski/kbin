<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post;

use App\Entity\Post;
use App\Entity\User;
use App\Event\Post\PostBeforePurgeEvent;
use App\Kbin\MessageBus\ImagePurgeMessage;
use App\Kbin\PostComment\PostCommentPurge;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class PostPurge
{
    public function __construct(
        private PostCommentPurge $postCommentPurge,
        private EventDispatcherInterface $eventDispatcher,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(User $user, Post $post): void
    {
        $this->eventDispatcher->dispatch(new PostBeforePurgeEvent($post, $user));

        $image = $post->image?->filePath;

        $sort = new Criteria(null, ['createdAt' => Criteria::DESC]);
        foreach ($post->comments->matching($sort) as $comment) {
            ($this->postCommentPurge)($user, $comment);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        if ($image) {
            $this->messageBus->dispatch(new ImagePurgeMessage($image));
        }
    }
}
