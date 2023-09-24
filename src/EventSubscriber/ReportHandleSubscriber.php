<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Report;
use App\Entity\User;
use App\Event\Entry\EntryDeletedEvent;
use App\Event\EntryComment\EntryCommentDeletedEvent;
use App\Event\Post\PostDeletedEvent;
use App\Event\PostComment\PostCommentDeletedEvent;
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
