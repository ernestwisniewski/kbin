<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EntryComment\EventSubscriber\EntryComment;

use App\Entity\EntryComment;
use App\Entity\User;

class EntryCommentDeletedEvent
{
    public function __construct(public EntryComment $comment, public ?User $user = null)
    {
    }
}
