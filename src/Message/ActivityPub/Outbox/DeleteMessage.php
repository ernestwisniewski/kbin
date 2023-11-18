<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Message\ActivityPub\Outbox;

use App\Message\Contracts\AsyncApMessageInterface;

class DeleteMessage implements AsyncApMessageInterface
{
    public function __construct(public array $payload, public int $userId, public int $magazineId)
    {
    }
}
