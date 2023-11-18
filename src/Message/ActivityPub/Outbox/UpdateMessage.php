<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Message\ActivityPub\Outbox;

use App\Kbin\MessageBus\Contracts\AsyncApMessageInterface;

class UpdateMessage implements AsyncApMessageInterface
{
    public function __construct(public int $id, public string $type)
    {
    }
}
