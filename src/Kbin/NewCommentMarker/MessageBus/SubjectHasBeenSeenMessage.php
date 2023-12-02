<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

namespace App\Kbin\NewCommentMarker\MessageBus;

use App\Kbin\MessageBus\Contracts\AsyncMessageInterface;

class SubjectHasBeenSeenMessage implements AsyncMessageInterface
{
    public function __construct(public int $userId, public int $subjectId, public string $subjectType)
    {
    }
}
