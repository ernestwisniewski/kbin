<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Contract;

use App\Entity\Contracts\ContentInterface;
use App\Entity\User;

interface DeleteContentServiceInterface
{
    public function __invoke(User $user, ContentInterface $subject);
}
