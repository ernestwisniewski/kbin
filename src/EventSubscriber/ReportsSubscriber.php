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
use App\Repository\ReportRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;

class ReportsSubscriber implements EventSubscriberInterface
{
    private ReportRepository $reportRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ReportRepository $reportRepository, EntityManagerInterface $entityManager)
    {
        $this->reportRepository = $reportRepository;
        $this->entityManager    = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryDeletedEvent::class            => 'onEntryDeleted',
            EntryBeforePurgeEvent::class        => 'onEntryBeforePurge',
            EntryCommentDeletedEvent::class     => 'onEntryCommentDeleted',
            EntryCommentBeforePurgeEvent::class => 'onEntryCommentBeforePurge',
            PostDeletedEvent::class             => 'onPostDeleted',
            PostBeforePurgeEvent::class         => 'onPostDeleted',
            PostCommentDeletedEvent::class      => 'onPostCommentDeleted',
            PostCommentBeforePurgeEvent::class  => 'onPostCommentBeforePurge',
        ];
    }

    private function handleReport(ReportInterface $subject)
    {
        $repo = $this->entityManager->getRepository($subject->getReportClassName());

        /**
         * @var $report Report
         */
        $report = $repo->findOneBy(
            [
                'subject' => $subject,
                'status'  => Report::STATUS_PENDING,
            ]
        );

        if ($report) {
            $report->setStatus(Report::STATUS_APPROVED);
        }

        // Notification for reporting, reported user
        // Reputation points for reporting user

        $this->entityManager->flush();
    }

    public function onEntryDeleted(EntryDeletedEvent $event): void
    {
        $this->handleReport($event->getEntry());
    }

    public function onEntryBeforePurge(EntryBeforePurgeEvent $event): void
    {
        $this->handleReport($event->getEntry());
    }

    public function onEntryCommentDeleted(EntryCommentDeletedEvent $event): void
    {
        $this->handleReport($event->getComment());
    }

    public function onEntryCommentBeforePurge(EntryCommentBeforePurgeEvent $event): void
    {
        $this->handleReport($event->getComment());
    }

    public function onPostDeleted(PostDeletedEvent $event): void
    {
        $this->handleReport($event->getPost());
    }

    public function onPostCommentDeleted(PostCommentDeletedEvent $event): void
    {
        $this->handleReport($event->getComment());
    }

    public function onPostCommentBeforePurge(PostCommentBeforePurgeEvent $event): void
    {
        $this->handleReport($event->getComment());
    }
}
