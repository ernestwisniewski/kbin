<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Magazine;
use App\Entity\Entry;
use App\Entity\User;
use DateTimeImmutable;
use LogicException;

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

    public int $page = 1;
    public ?Magazine $magazine = null;
    public ?User $user = null;
    public ?string $type = null;
    public string $sortOption = EntryRepository::SORT_DEFAULT;
    public string $time = EntryRepository::TIME_DEFAULT;
    public string $visibility = Entry::VISIBILITY_VISIBLE;
    public bool $subscribed = false;

    public function __construct(int $page)
    {
        $this->page = $page;
    }

    public function setType(?string $type): self
    {
        if ($type) {
            $this->type = $type;
        }

        return $this;
    }

    public function showSortOption(?string $sortOption): self
    {
        if ($sortOption) {
            $this->sortOption = $this->translateSort($sortOption);
        }

        return $this;
    }

    public function translateSort(?string $value): string
    {
        //@todo
        $routes = [
            'wazne'       => Criteria::SORT_HOT,
            'wschodzace'  => Criteria::SORT_TOP,
            'aktywne'     => Criteria::SORT_ACTIVE,
            'najnowsze'   => Criteria::SORT_NEW,
            'komentowane' => Criteria::SORT_COMMENTED,
        ];

        return $routes[$value] ?? $routes['aktywne'];
    }

    public function translateTime(?string $value): ?string
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
            null       => null,
        ];

        return $routes[$value] ?? $value;
    }

    public function translateType(?string $value): ?string
    {
        //@todo
        $routes = [
            'artykul' => Entry::ENTRY_TYPE_ARTICLE,
            'link'    => Entry::ENTRY_TYPE_LINK,
            'video'   => Entry::ENTRY_TYPE_VIDEO,
            'foto'    => Entry::ENTRY_TYPE_IMAGE,
            null      => null,
        ];

        return $routes[$value] ?? $value;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function setTime(?string $time): self
    {
        if ($time) {
            $this->time = $time;
        } else {
            $this->time = EntryRepository::TIME_DEFAULT;
        }

        return $this;
    }

    public function getSince(): DateTimeImmutable
    {
        $since = new DateTimeImmutable('@'.time());

        return match ($this->time) {
            Criteria::TIME_YEAR => $since->modify('-1 year'),
            Criteria::TIME_MONTH => $since->modify('-1 month'),
            Criteria::TIME_WEEK => $since->modify('-1 week'),
            Criteria::TIME_DAY => $since->modify('-1 day'),
            Criteria::TIME_12_HOURS => $since->modify('-12 hours'),
            Criteria::TIME_6_HOURS => $since->modify('-6 hours'),
            default => throw new LogicException(),
        };
    }
}
