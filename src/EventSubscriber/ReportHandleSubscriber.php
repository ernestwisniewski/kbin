<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Report;
use App\Entity\User;
use App\Kbin\Entry\EventSubscriber\Event\EntryDeletedEvent;
use App\Kbin\EntryComment\EventSubscriber\Event\EntryCommentDeletedEvent;
use App\Kbin\Post\EventSubscriber\Event\PostDeletedEvent;
use App\Kbin\PostComment\EventSubscriber\Event\PostCommentDeletedEvent;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReportHandleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ReportRepository $repository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryDeletedEvent::class => 'onEntryDeleted',
            EntryCommentDeletedEvent::class => 'onEntryCommentDeleted',
            PostDeletedEvent::class => 'onPostDeleted',
            PostCommentDeletedEvent::class => 'onPostCommentDeleted',
        ];
    }

    public function onEntryDeleted(EntryDeletedEvent $event): void
    {
        $this->handleReport($event->entry, $event->user);
        $this->entityManager->flush();
    }

    public function onEntryCommentDeleted(EntryCommentDeletedEvent $event): void
    {
        $this->handleReport($event->comment, $event->user);
        $this->entityManager->flush();
    }

    public function onPostDeleted(PostDeletedEvent $event): void
    {
        $this->handleReport($event->post, $event->user);
        $this->entityManager->flush();
    }

    public function onPostCommentDeleted(PostCommentDeletedEvent $event): void
    {
        $this->handleReport($event->comment, $event->user);
        $this->entityManager->flush();
    }

    private function handleReport(ReportInterface $subject, ?User $user): void
    {
        $report = $this->repository->findBySubject($subject);

        if (!$report) {
            return;
        }

        // If the user deletes their own post when a report has been lodged against it
        //    the report should not be considered approved
        if ($user && $user->getId() === $subject->getUser()->getId()) {
            $report->status = Report::STATUS_CLOSED;
        } else {
            $report->status = Report::STATUS_APPROVED;
            $report->consideredBy = $user;
            $report->consideredAt = new \DateTimeImmutable();
        }

        // @todo Notification for reporting, reported user
        // @todo Reputation points for reporting user
    }
}
