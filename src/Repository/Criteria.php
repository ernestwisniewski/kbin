<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Magazine;
use App\Entity\Entry;
use App\Entity\User;
use App\PageView\EntryPageView;

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

    public const TIME_6_HOURS = '6hours';
    public const TIME_12_HOURS = '12hours';
    public const TIME_DAY = 'day';
    public const TIME_WEEK = 'week';
    public const TIME_MONTH = 'month';
    public const TIME_YEAR = 'year';
    public const TIME_ALL = '∞';

    public const FRONT_PAGE_OPTIONS = [
        self::FRONT_FEATURED,
        self::FRONT_SUBSCRIBED,
        self::FRONT_ALL,
        self::FRONT_MODERATED,
    ];

    public const TIME_OPTIONS = [
        self::TIME_6_HOURS,
        self::TIME_12_HOURS,
        self::TIME_DAY,
        self::TIME_WEEK,
        self::TIME_MONTH,
        self::TIME_YEAR,
        self::TIME_ALL,
    ];

    private int $page = 1;
    private ?Magazine $magazine = null;
    private ?User $user = null;
    private ?string $type = null;
    private string $sortOption = EntryRepository::SORT_DEFAULT;
    private string $time = EntryRepository::TIME_DEFAULT;
    private string $visibility = Entry::VISIBILITY_VISIBLE;
    private bool $subscribed = false;

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

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
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
        $this->sortOption = $this->translateSort($sortOption);

        return $this;
    }

    public function showSubscribed(): self
    {
        $this->subscribed = true;

        return $this;
    }

    public function isSubscribed(): bool
    {
        return $this->subscribed;
    }

    public function translateSort(string $value): string
    {
        //@todo
        $routes = [
            'wazne'       => Criteria::SORT_HOT,
            'wschodzace'  => Criteria::SORT_TOP,
            'aktywne'     => Criteria::SORT_ACTIVE,
            'najnowsze'   => Criteria::SORT_NEW,
            'komentowane' => Criteria::SORT_COMMENTED,
        ];

        if (in_array($value, $routes)) {
            return $value;
        }

        return $routes[$value];
    }

    public function translateTime(string $value): string
    {
        //@todo
        $routes = [
            '6h'       => Criteria::TIME_6_HOURS,
            '12h'      => Criteria::TIME_12_HOURS,
            '1d'       => Criteria::TIME_DAY,
            '1w'       => Criteria::TIME_WEEK,
            '1m'       => Criteria::TIME_MONTH,
            '1y'       => Criteria::TIME_YEAR,
            '∞'        => Criteria::TIME_ALL,
            'wszystko' => Criteria::TIME_ALL,
        ];

        if (in_array($value, $routes)) {
            return $value;
        }

        return $routes[$value];
    }

    public function translateType(string $value): string
    {
        //@todo
        $routes = [
            'artykul' => Entry::ENTRY_TYPE_ARTICLE,
            'link'    => Entry::ENTRY_TYPE_LINK,
            'video'   => Entry::ENTRY_TYPE_VIDEO,
            'foto'    => Entry::ENTRY_TYPE_IMAGE,
        ];

        if (in_array($value, $routes)) {
            return $value;
        }

        return $routes[$value];
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function setTime(string $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getSince()
    {
        $since = new \DateTimeImmutable('@'.time());

        return match ($this->getTime()) {
            Criteria::TIME_YEAR => $since->modify('-1 year'),
            Criteria::TIME_MONTH => $since->modify('-1 month'),
            Criteria::TIME_WEEK => $since->modify('-1 week'),
            Criteria::TIME_DAY => $since->modify('-1 day'),
            Criteria::TIME_12_HOURS => $since->modify('-12 hours'),
            Criteria::TIME_6_HOURS => $since->modify('-6 hours'),
            default => throw new \LogicException(),
        };
    }
}
