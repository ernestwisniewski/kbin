<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service;

use App\Factory\ReportFactory;
use App\Kbin\Factory\DeleteServiceFactory;
use App\Kbin\Factory\RestoreServiceFactory;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ReportManager
{
    public function __construct(
        private readonly ReportFactory $factory,
        private readonly ReportRepository $repository,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface $entityManager,
        private readonly DeleteServiceFactory $deleteServiceFactory,
        private readonly RestoreServiceFactory $restoreServiceFactory,
    ) {
    }
}
