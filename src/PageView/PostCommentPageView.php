<?php declare(strict_types = 1);

namespace App\PageView;

use App\Entity\Post;
use App\Repository\Criteria;

class PostCommentPageView extends Criteria
{
    public const SORT_OPTIONS = [
        self::SORT_NEW,
        self::SORT_TOP,
    ];

    public ?Post $post = null;
    public bool $onlyParents = true;
}
