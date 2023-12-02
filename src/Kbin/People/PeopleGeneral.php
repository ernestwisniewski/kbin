<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\People;

use App\Repository\UserRepository;

readonly class PeopleGeneral
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(bool $federated = false): array
    {
        if ($federated) {
            return $this->userRepository->findWithAbout(UserRepository::USERS_REMOTE);
        }

        return $this->userRepository->findWithAbout(UserRepository::USERS_LOCAL);
    }
}
