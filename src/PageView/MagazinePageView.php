<?php

declare(strict_types=1);

namespace App\PageView;

use App\Repository\Criteria;

class MagazinePageView extends Criteria
{
    public const SORT_THREADS = 'threads';
    public const SORT_COMMENTS = 'comments';
    public const SORT_POSTS = 'posts';

    public const FIELDS_NAMES = 'names';
    public const FIELDS_NAMES_DESCRIPTIONS = 'names_descriptions';

    public const ADULT_HIDE = 'hide';
    public const ADULT_SHOW = 'show';
    public const ADULT_ONLY = 'only';

    public ?string $query = null;
    public string $fields = self::FIELDS_NAMES;

    public function __construct(
        public int $page,
        public string $sortOption,
        public string $federation,
        public string $adult,
    ) {
        parent::__construct($page);
        $this->resolveSort($sortOption);
    }

    public function showOnlyLocalMagazines(): bool
    {
        return self::AP_LOCAL === $this->federation;
    }

    protected function routes(): array
    {
        return array_merge(
            parent::routes(),
            [
                'threads' => self::SORT_THREADS,
                'comments' => self::SORT_COMMENTS,
                'posts' => self::SORT_POSTS,
            ],
        );
    }
}
