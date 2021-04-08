<?php declare(strict_types=1);

namespace App\PageView;

use App\Repository\Criteria;
use App\Entity\Entry;

class EntryCommentPageView extends Criteria
{
    public const SORT_OPTIONS = [
        self::SORT_NEW,
        self::SORT_TOP,
    ];

    public ?Entry $entry = null;
    public bool $onlyParents = true;

}
