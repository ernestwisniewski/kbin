<?php declare(strict_types = 1);

namespace App\PageView;

use App\Entity\Entry;
use App\Repository\Criteria;

class EntryCommentPageView extends Criteria
{
    public const SORT_OPTIONS = [
        self::SORT_NEW,
        self::SORT_TOP,
    ];

    public ?Entry $entry = null;
    public bool $onlyParents = true;

}
