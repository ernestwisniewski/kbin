<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\PageView;

use App\Repository\Criteria;

class PostPageView extends Criteria
{
    public const SORT_OPTIONS = [
        self::SORT_ACTIVE,
        self::SORT_HOT,
        self::SORT_NEW,
        self::SORT_TOP,
        self::SORT_COMMENTED,
    ];

    public bool $stickiesFirst = false;
}
