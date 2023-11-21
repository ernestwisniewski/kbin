<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\Donor;

use App\Entity\Donor;
use App\Kbin\Donor\DTO\DonorDto;
use App\Repository\DonorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

readonly class DonorCreate
{
    public function __construct(
        private DonorRepository $donorRepository,
        private RateLimiterFactory $contactLimiter,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(DonorDto $donorDto): ?Donor
    {
        $limiter = $this->contactLimiter->create($donorDto->ip);
        if (false === $limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        if ($this->donorRepository->findOneByEmail($donorDto->email)) {
            return null;
        }

        $donor = new Donor();
        $donor->email = $donorDto->email;
        $donor->username = $donorDto->username;
        $donor->url = $donorDto->url;
        $donor->isActive = false;
        $donor->user = $donorDto->user;

        $this->entityManager->persist($donor);
        $this->entityManager->flush();

        return $donor;
    }
}
