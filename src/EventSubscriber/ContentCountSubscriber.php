<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\EntryComment\EntryCommentCreatedEvent;
use App\Event\EntryComment\EntryCommentDeletedEvent;
use App\Event\EntryComment\EntryCommentPurgedEvent;
use App\Event\Post\PostDeletedEvent;
use App\Event\PostComment\PostCommentCreatedEvent;
use App\Event\PostComment\PostCommentDeletedEvent;
use App\Event\PostComment\PostCommentPurgedEvent;
use App\Kbin\Entry\EventSubscriber\Event\EntryDeletedEvent;
use App\Repository\EntryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentCountSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntryRepository $entryRepository,
        private readonly PostRepository $postRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryDeletedEvent::class => 'onEntryDeleted',
            PostDeletedEvent::class => 'onPostDeleted',
            EntryCommentCreatedEvent::class => 'onEntryCommentCreated',
            EntryCommentDeletedEvent::class => 'onEntryCommentDeleted',
            EntryCommentPurgedEvent::class => 'onEntryCommentPurged',
            PostCommentCreatedEvent::class => 'onPostCommentCreated',
            PostCommentDeletedEvent::class => 'onPostCommentDeleted',
            PostCommentPurgedEvent::class => 'onPostCommentPurged',
        ];
    }

    public function onEntryDeleted(EntryDeletedEvent $event): void
    {
        $event->entry->magazine->updateEntryCounts();

        $this->entityManager->flush();
    }

    public function onPostDeleted(PostDeletedEvent $event): void
    {
        $event->post->magazine->updatePostCounts();

        $this->entityManager->flush();
    }

    public function onEntryCommentCreated(EntryCommentCreatedEvent $event): void
    {
        $magazine = $event->comment->entry->magazine;
        $magazine->entryCommentCount = $this->entryRepository->countEntryCommentsByMagazine($magazine);

        $this->entityManager->flush();
    }

    public function onEntryCommentDeleted(EntryCommentDeletedEvent $event): void
    {
        $magazine = $event->comment->entry->magazine;
        $magazine->entryCommentCount = $this->entryRepository->countEntryCommentsByMagazine($magazine) - 1;

        $event->comment->entry->updateCounts();

        $this->entityManager->flush();
    }

    public function onEntryCommentPurged(EntryCommentPurgedEvent $event): void
    {
        $event->magazine->entryCommentCount = $this->entryRepository->countEntryCommentsByMagazine($event->magazine);

        $this->entityManager->flush();
    }

    public function onPostCommentCreated(PostCommentCreatedEvent $event): void
    {
        $magazine = $event->comment->post->magazine;
        $magazine->postCommentCount = $this->postRepository->countPostCommentsByMagazine($magazine);

        $this->entityManager->flush();
    }

    public function onPostCommentDeleted(PostCommentDeletedEvent $event): void
    {
        $magazine = $event->comment->post->magazine;
        $magazine->postCommentCount = $this->postRepository->countPostCommentsByMagazine($magazine) - 1;

        $event->comment->post->updateCounts();

        $this->entityManager->flush();
    }

    public function onPostCommentPurged(PostCommentPurgedEvent $event): void
    {
        $event->magazine->postCommentCount = $this->postRepository->countPostCommentsByMagazine($event->magazine);

        $this->entityManager->flush();
    }
}
