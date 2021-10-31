<?php declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Report;
use App\Event\Entry\EntryBeforePurgeEvent;
use App\Event\Entry\EntryDeletedEvent;
use App\Event\EntryComment\EntryCommentBeforePurgeEvent;
use App\Event\EntryComment\EntryCommentDeletedEvent;
use App\Event\Post\PostBeforePurgeEvent;
use App\Event\Post\PostDeletedEvent;
use App\Event\PostComment\PostCommentBeforePurgeEvent;
use App\Event\PostComment\PostCommentDeletedEvent;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReportHandleSubscriber implements EventSubscriberInterface
{
    public function __construct(private ReportRepository $repository, private EntityManagerInterface $entityManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryDeletedEvent::class            => 'onEntryDeleted',
            EntryBeforePurgeEvent::class        => 'onEntryBeforePurge',
            EntryCommentDeletedEvent::class     => 'onEntryCommentDeleted',
            EntryCommentBeforePurgeEvent::class => 'onEntryCommentBeforePurge',
            PostDeletedEvent::class             => 'onPostDeleted',
            PostBeforePurgeEvent::class         => 'onPostBeforePurge',
            PostCommentDeletedEvent::class      => 'onPostCommentDeleted',
            PostCommentBeforePurgeEvent::class  => 'onPostCommentBeforePurge',
        ];
    }

    public function onEntryDeleted(EntryDeletedEvent $event): void
    {
        $this->handleReport($event->entry);
        $this->entityManager->flush();
    }

    private function handleReport(ReportInterface $subject): ?Report
    {
        $report = $this->repository->findBySubject($subject);

        if (!$report) {
            return null;
        }

        $report->status = Report::STATUS_APPROVED;

        // @todo Notification for reporting, reported user
        // @todo Reputation points for reporting user

        return $report;
    }

    public function onEntryBeforePurge(EntryBeforePurgeEvent $event): void
    {
        $report = $this->handleReport($event->entry);
        if (!$report) {
            return;
        }

        $report->clearSubject();
        $this->entityManager->flush();
    }

    public function onEntryCommentDeleted(EntryCommentDeletedEvent $event): void
    {
        $this->handleReport($event->comment);
        $this->entityManager->flush();
    }

    public function onEntryCommentBeforePurge(EntryCommentBeforePurgeEvent $event): void
    {
        $this->handleReport($event->comment);
        $this->entityManager->flush();
    }

    public function onPostDeleted(PostDeletedEvent $event): void
    {
        $this->handleReport($event->post);
        $this->entityManager->flush();
    }

    public function onPostBeforePurge(PostBeforePurgeEvent $event): void
    {
        $this->handleReport($event->post);
        $this->entityManager->flush();
    }

    public function onPostCommentDeleted(PostCommentDeletedEvent $event): void
    {
        $this->handleReport($event->comment);
        $this->entityManager->flush();
    }

    public function onPostCommentBeforePurge(PostCommentBeforePurgeEvent $event): void
    {
        $report = $this->handleReport($event->comment);
        if (!$report) {
            return;
        }

        $report->clearSubject();
        $this->entityManager->flush();
    }
}
