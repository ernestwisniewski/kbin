<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\EntryComment;

use App\Entity\Entry;
use App\Repository\Criteria;

class EntryCommentPageView extends Criteria
{
    public const SORT_OPTIONS = [
        self::SORT_NEW,
        self::SORT_TOP,
        self::SORT_HOT,
        self::SORT_NEW,
        self::SORT_OLD,
    ];

    public ?Entry $entry = null;
    public bool $onlyParents = true;
}
