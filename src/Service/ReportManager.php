<?php declare(strict_types = 1);

namespace App\Service;

use App\DTO\ReportDto;
use App\Entity\Report;
use App\Entity\User;
use App\Event\Report\ReportRejectedEvent;
use App\Event\Report\SubjectReportedEvent;
use App\Exception\SubjectHasBeenReportedException;
use App\Factory\ReportFactory;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ReportManager
{
    public function __construct(
        private ReportFactory $factory,
        private ReportRepository $repository,
        private EventDispatcherInterface $dispatcher,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function report(ReportDto $dto, User $reporting): Report
    {
        $existed = $report = $this->repository->findBySubject($dto->getSubject());

        if ($report) {
            if ($report->reporting === $reporting) {
                throw new SubjectHasBeenReportedException();
            }
        }

        if (!$report) {
            $report = $this->factory->createFromDto($dto);
        } elseif ($report->status === Report::STATUS_PENDING) {
            $report->increaseWeight();
        }

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        if (!$existed) {
            $this->dispatcher->dispatch(new SubjectReportedEvent($report));
        }

        return $report;
    }

    public function reject(Report $report)
    {
        $report->status = Report::STATUS_REJECTED;

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new ReportRejectedEvent($report));
    }
}
