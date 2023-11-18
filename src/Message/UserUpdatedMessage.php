<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Message;

use App\Message\Contracts\SendConfirmationEmailInterface;

class UserUpdatedMessage implements SendConfirmationEmailInterface
{
    public function __construct(public int $userId)
    {
    }
}
