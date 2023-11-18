<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Entity\User;
use App\Repository\SearchRepository;
use Twig\Extension\RuntimeExtensionInterface;

class CounterExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly SearchRepository $searchRepository)
    {
    }

    public function countUserBoosts(User $user): int
    {
        return $this->searchRepository->countBoosts($user);
    }

    public function countUserModerated(User $user): int
    {
        return $this->searchRepository->countModerated($user);
    }
}
