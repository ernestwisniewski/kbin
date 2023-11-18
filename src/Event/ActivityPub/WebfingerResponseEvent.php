<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Event\ActivityPub;

use App\ActivityPub\JsonRd;

class WebfingerResponseEvent
{
    public function __construct(public JsonRd $jsonRd)
    {
    }
}
