<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Report;

use App\Entity\Report;
use App\Entity\User;
use App\Event\Report\ReportRejectedEvent;
use App\Kbin\Factory\RestoreServiceFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class ReportReject
{
    public function __construct(
        private RestoreServiceFactory $restoreServiceFactory,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Report $report, User $moderator): void
    {
        $restoreService = $this->restoreServiceFactory->create($report->getSubject());

        $report->status = Report::STATUS_REJECTED;
        $report->consideredBy = $moderator;
        $report->consideredAt = new \DateTimeImmutable();

        if ($report->getSubject()->isTrashed()) {
            $restoreService($moderator, $report->getSubject());
        }

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new ReportRejectedEvent($report));
    }
}
