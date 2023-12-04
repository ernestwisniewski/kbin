<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Report;

use App\Entity\Report;
use App\Entity\User;
use App\Event\Report\SubjectReportedEvent;
use App\Factory\ReportFactory;
use App\Kbin\Report\DTO\ReportDto;
use App\Kbin\Report\Exception\SubjectHasBeenReportedException;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class ReportCreate
{
    public function __construct(
        private ReportFactory $reportFactory,
        private ReportRepository $reportRepository,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(ReportDto $dto, User $reporting): Report
    {
        $report = $this->reportRepository->findBySubject($dto->getSubject());

        if ($report) {
            $report->increaseWeight();
            $this->entityManager->flush();

            throw new SubjectHasBeenReportedException();
        }

        $report = $this->reportFactory->createFromDto($dto);
        $report->reporting = $reporting;

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new SubjectReportedEvent($report));

        return $report;
    }
}
