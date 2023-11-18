<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Service;

use App\Kbin\Vote\Factory\VoteFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class VoteManager
{
    public function __construct(
        private readonly VoteFactory $factory,
        private readonly RateLimiterFactory $voteLimiter,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface $entityManager
    ) {
    }
}
