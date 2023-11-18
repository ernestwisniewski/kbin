<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User\UserDeleteRequest;

use App\Entity\User;

readonly class UserPauseAccountRevoke
{
    public function __construct(
        private UserDeleteRequestRevoke $userDeleteRequestRevoke,
    ) {
    }

    public function __invoke(User $user): void
    {
        ($this->userDeleteRequestRevoke)($user);
    }
}
