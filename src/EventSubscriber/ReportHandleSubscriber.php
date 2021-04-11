<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Report;
use App\Event\EntryBeforePurgeEvent;
use App\Event\EntryCommentBeforePurgeEvent;
use App\Event\EntryCommentDeletedEvent;
use App\Event\EntryDeletedEvent;
use App\Event\PostBeforePurgeEvent;
use App\Event\PostCommentBeforePurgeEvent;
use App\Event\PostCommentDeletedEvent;
use App\Event\PostDeletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReportHandleSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
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
        $repo = $this->entityManager->getRepository(get_class($subject).'Report');
        /**
         * @var $report Report
         */
        $report = $repo->findPendingBySubject($subject);

        if (!$report) {
            return null;
        }

        $report->status = Report::STATUS_APPROVED;

        // Notification for reporting, reported user
        // Reputation points for reporting user

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
