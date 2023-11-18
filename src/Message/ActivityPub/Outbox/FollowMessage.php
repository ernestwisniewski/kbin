<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Message\ActivityPub\Outbox;

class FollowMessage
{
    public function __construct(
        public int $followerId,
        public int $followingId,
        public bool $unfollow = false,
        public bool $magazine = false
    ) {
    }
}
