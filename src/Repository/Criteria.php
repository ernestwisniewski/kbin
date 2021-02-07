<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Magazine;
use App\Entity\Entry;
use App\Entity\User;

abstract class Criteria
{
    const ENTRY_TYPE_ARTICLE = 'article';
    const ENTRY_TYPE_LINK = 'link';

    public const FRONT_FEATURED = 'featured';
    public const FRONT_SUBSCRIBED = 'subscribed';
    public const FRONT_ALL = 'all';
    public const FRONT_MODERATED = 'moderated';
    public const SORT_ACTIVE = 'active';
    public const SORT_HOT = 'hot';
    public const SORT_NEW = 'new';
    public const SORT_TOP = 'top';
    public const SORT_COMMENTED = 'commented';
    public const TIME_DAY = 'day';
    public const TIME_WEEK = 'week';
    public const TIME_MONTH = 'month';
    public const TIME_YEAR = 'year';
    public const TIME_ALL = 'all';

    public const FRONT_PAGE_OPTIONS = [
        self::FRONT_FEATURED,
        self::FRONT_SUBSCRIBED,
        self::FRONT_ALL,
        self::FRONT_MODERATED,
    ];

    public const TIME_OPTIONS = [
        self::TIME_DAY,
        self::TIME_WEEK,
        self::TIME_MONTH,
        self::TIME_YEAR,
        self::TIME_ALL,
    ];

    private int $page = 1;
    private ?Magazine $magazine = null;
    private ?User $user = null;
    private string $sortOption = EntryRepository::SORT_DEFAULT;

    public function __construct(int $page)
    {
        $this->page = $page;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function showMagazine(Magazine $magazine): self
    {
        $this->magazine = $magazine;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }


    public function showUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSortOption(): string
    {
        return $this->sortOption;
    }

    public function showSortOption(string $sortOption): self
    {
        $this->sortOption = $this->translate($sortOption);

        return $this;
    }

    public function translate(string $value): string
    {
        //@todo
        $routes = [
            'wazne'       => Criteria::SORT_HOT,
            'najnowsze'   => Criteria::SORT_NEW,
            'wschodzace'  => Criteria::SORT_TOP,
            'komentowane' => Criteria::SORT_COMMENTED,
        ];

        if (in_array($value, $routes)) {
            return $value;
        }

        return $routes[$value];
    }

}
