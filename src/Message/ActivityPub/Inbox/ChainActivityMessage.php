<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Message\ActivityPub\Inbox;

use App\Kbin\MessageBus\Contracts\AsyncApMessageInterface;

class ChainActivityMessage implements AsyncApMessageInterface
{
    public function __construct(
        public array $chain,
        public ?array $parent = null,
        public ?array $announce = null,
        public ?array $like = null
    ) {
    }
}
