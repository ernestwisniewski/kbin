<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\PostComment;

use App\Entity\Post;
use App\Repository\Criteria;

class PostCommentPageView extends Criteria
{
    public const SORT_OPTIONS = [
        self::SORT_NEW,
        self::SORT_OLD,
        self::SORT_TOP,
    ];

    public ?Post $post = null;
    public bool $onlyParents = true;
}
