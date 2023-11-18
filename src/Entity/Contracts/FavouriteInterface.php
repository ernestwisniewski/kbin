<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Entity\Contracts;

use App\Entity\User;

interface FavouriteInterface extends ContentInterface
{
    public function getId(): ?int;

    public function getUser(): ?User;

    public function updateCounts(): self;

    public function isFavored(User $user): bool;

    public function updateRanking(): void;
}
