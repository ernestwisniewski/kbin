<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\MessageBus;

use App\Kbin\MessageBus\Contracts\AsyncMessageInterface;

class LinkEmbedMessage implements AsyncMessageInterface
{
    public function __construct(public string $body)
    {
    }
}
