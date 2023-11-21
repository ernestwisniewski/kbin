<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Donor\Factory;

use App\Entity\User;
use App\Kbin\Donor\DTO\DonorDto;
use App\Repository\DonorRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class DonorFactory
{
    public function __construct(private DonorRepository $donorRepository, private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function createDto(?string $email, ?User $user = null): DonorDto
    {
        $donor = $this->donorRepository->findOneBy(['email' => $email]);

        if ($donor) {
            return new DonorDto($donor->email, $donor->username, $donor->url, $donor->isActive, $user);
        } elseif ($user) {
            return new DonorDto(
                $user->email,
                $user->username,
                $this->urlGenerator->generate('user_overview', ['username' => $user->username]),
                false,
                $user
            );
        }

        return new DonorDto();
    }
}
