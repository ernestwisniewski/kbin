<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Message;

use App\Entity\MessageThread;
use App\Repository\Criteria;

class MessageThreadPageView extends Criteria
{
    public const SORT_OPTIONS = [
        self::SORT_NEW,
        self::SORT_OLD,
    ];

    public ?MessageThread $thread = null;
}
