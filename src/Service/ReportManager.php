<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\ReportDto;
use App\Entity\Contracts\ReportInterface;
use App\Entity\Report;
use App\Entity\User;
use App\Event\ReportRejectedEvent;
use App\Event\SubjectReportedEvent;
use App\Exception\SubjectHasBeenReported;
use App\Factory\ReportFactory;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ReportManager
{
    private ReportFactory $reportFactory;
    private EventDispatcherInterface $eventDispatcher;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ReportFactory $reportFactory,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager
    ) {
        $this->reportFactory = $reportFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
    }

    public function report(ReportDto $dto, User $reporting): Report
    {
        $repository = $this->entityManager->getRepository($dto->getSubject()->getReportClassName());

        /**
         * @var $report Report
         */
        $existed = $report = $repository->findOneBy(['subject' => $dto->getSubject()]);

        if ($report) {
            if ($repository->findOneBy(
                ['subject' => $dto->getSubject(), 'reporting' => $reporting]
            )) {
                throw new SubjectHasBeenReported();
            }
        }

        if (!$report) {
            $report = $this->reportFactory->createFromDto($dto, $reporting);
        } elseif ($report->getStatus() === Report::STATUS_PENDING) {
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
        $report->setStatus(Report::STATUS_REJECTED);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new ReportRejectedEvent($report));
    }
}
