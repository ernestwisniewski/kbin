<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Message\ActivityPub;

use App\Kbin\MessageBus\Contracts\AsyncApMessageInterface;

class CreateActorMessage implements AsyncApMessageInterface
{
    public function __construct(public string $handle)
    {
    }
}
