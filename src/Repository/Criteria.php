<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Magazine;
use App\Entity\Entry;
use App\Entity\User;

class Criteria
{
    const ENTRY_TYPE_ARTICLE = 'artykul';
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

    public const SORT_OPTIONS = [
        self::SORT_ACTIVE,
        self::SORT_HOT,
        self::SORT_NEW,
        self::SORT_TOP,
        self::SORT_COMMENTED,
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
    private ?Entry $entry = null;
    private ?User $user = null;
    private string $orderBy = self::FRONT_ALL;


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

    public function setMagazine(Magazine $magazine): self
    {
        $this->magazine = $magazine;

        return $this;
    }

    public function getEntry(): ?Entry
    {
        return $this->entry;
    }

    public function setEntry(Entry $entry): self
    {
        $this->entry = $entry;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    public function orderBy(string $sortBy): self
    {
        $this->orderBy = $sortBy;

        return $this;
    }
}
