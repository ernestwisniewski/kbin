<?php declare(strict_types=1);

namespace App\Service;

use App\Exception\SubjectHasBeenReportedException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\SubjectReportedEvent;
use App\Event\ReportRejectedEvent;
use App\Factory\ReportFactory;
use App\DTO\ReportDto;
use App\Entity\Report;
use App\Entity\User;

class ReportManager
{
    public function __construct(
        private ReportFactory $reportFactory,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function report(ReportDto $dto, User $reporting): Report
    {
        $repository = $this->entityManager->getRepository(get_class($dto->getSubject()).'Report');

        /**
         * @var $report Report
         */
        $existed = $report = $repository->findBySubject($dto->getSubject());

        if ($report) {
            if ($report->reporting === $reporting) {
                throw new SubjectHasBeenReportedException();
            }
        }

        if (!$report) {
            $report = $this->reportFactory->createFromDto($dto, $reporting);
        } elseif ($report->status === Report::STATUS_PENDING) {
            $report->increaseWeight();
        }

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        if (!$existed) {
            $this->eventDispatcher->dispatch(new SubjectReportedEvent($report));
        }

        return $report;
    }

    public function reject(Report $report)
    {
        $report->status = Report::STATUS_REJECTED;

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new ReportRejectedEvent($report));
    }
}
