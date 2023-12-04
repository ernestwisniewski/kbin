<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\PostComment\EventSubscriber;

use App\Kbin\PostComment\EventSubscriber\Event\PostCommentCreatedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentDeletedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentPurgedEvent;
use App\Repository\PostRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class PostCommentCounterSubscriber
{
    public function __construct(private PostRepository $postRepository)
    {
    }

    #[AsEventListener(event: PostCommentPurgedEvent::class)]
    public function onPostCommentPurged(PostCommentPurgedEvent $event): void
    {
        $event->magazine->postCommentCount = $this->postRepository->countPostCommentsByMagazine($event->magazine);
    }

    #[AsEventListener(event: PostCommentDeletedEvent::class)]
    public function onPostCommentDeleted(PostCommentDeletedEvent $event): void
    {
        $magazine = $event->comment->post->magazine;
        $magazine->postCommentCount = $this->postRepository->countPostCommentsByMagazine($magazine) - 1;

        $event->comment->post->updateCounts();
    }

    #[AsEventListener(event: PostCommentCreatedEvent::class)]
    public function onPostCommentCreated(PostCommentCreatedEvent $event): void
    {
        $magazine = $event->comment->post->magazine;
        $magazine->postCommentCount = $this->postRepository->countPostCommentsByMagazine($magazine);
    }
}
