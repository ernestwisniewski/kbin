<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Post\EventSubscriber;

use App\Kbin\Post\EventSubscriber\Event\PostBeforeDeletedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostBeforePurgeEvent;
use App\Kbin\Post\EventSubscriber\Event\PostCreatedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostDeletedEvent;
use App\Repository\PostRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class PostCounterSubscriber
{
    public function __construct(private PostRepository $postRepository,)
    {
    }

    #[AsEventListener(event: PostBeforePurgeEvent::class)]
    public function onPostBeforePurge(PostBeforePurgeEvent $event): void
    {
        $event->post->magazine->postCount = $this->postRepository->countPostsByMagazine(
                $event->post->magazine
            ) - 1;
    }

    #[AsEventListener(event: PostDeletedEvent::class)]
    public function onPostDeleted(PostDeletedEvent $event): void
    {
        $event->post->magazine->updatePostCounts();
    }

    #[AsEventListener(event: PostCreatedEvent::class)]
    public function onPostCreated(PostCreatedEvent $event): void
    {
        $event->post->magazine->postCount = $this->postRepository->countPostsByMagazine($event->post->magazine);
    }
}