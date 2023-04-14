<?php

declare(strict_types=1);

namespace App\PageView;

use App\Repository\Criteria;

class MagazinePageView extends Criteria
{
    public const SORT_OPTIONS = [
        self::SORT_ACTIVE,
        self::SORT_HOT,
        self::SORT_NEW,
        self::SORT_TOP,
    ];
}
