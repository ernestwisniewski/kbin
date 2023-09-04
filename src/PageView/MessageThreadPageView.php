<?php

declare(strict_types=1);

namespace App\PageView;

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
