<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Message\ActivityPub\Outbox;

use App\Message\Contracts\AsyncApMessageInterface;

class AnnounceMessage implements AsyncApMessageInterface
{
    public function __construct(
        public int $userId,
        public int $objectId,
        public string $objectType,
        public bool $removeAnnounce = false
    ) {
    }
}
