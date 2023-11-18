<?php

declare(strict_types=1);

namespace App\Service;

use App\Factory\VoteFactory;
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
