<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Report;

use App\Entity\Report;
use App\Entity\User;
use App\Event\Report\ReportApprovedEvent;
use App\Kbin\Factory\DeleteServiceFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class ReportAccept
{
    public function __construct(
        private DeleteServiceFactory $deleteServiceFactory,
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Report $report, User $moderator): void
    {
        $deleteService = $this->deleteServiceFactory->create($report->getSubject());

        $report->status = Report::STATUS_APPROVED;
        $report->consideredBy = $moderator;
        $report->consideredAt = new \DateTimeImmutable();

        $deleteService($moderator, $report->getSubject());

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new ReportApprovedEvent($report));
    }
}
