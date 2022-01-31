<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Entry;
use App\Entity\Magazine;
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
    public const SORT_NEW = 'newest';
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
    public ?int $perPage = null;
    public bool $moderated = false;
    public ?string $type = null;
    public string $sortOption = EntryRepository::SORT_DEFAULT;
    public string $time = EntryRepository::TIME_DEFAULT;
    public string $visibility = Entry::VISIBILITY_VISIBLE;
    public bool $subscribed = false;
    public ?string $tag = null;
    public ?string $domain = null;

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

    public function setTag(string $name): self
    {
        $this->tag = $name;

        return $this;
    }

    public function setDomain(string $name): self
    {
        $this->domain = $name;

        return $this;
    }

    public function showSortOption(?string $sortOption): self
    {
        if ($sortOption) {
            $this->sortOption = $sortOption;
        }

        return $this;
    }

    public function resolveSort(?string $value): string
    {
        //@todo
        $routes = [
            'top'       => Criteria::SORT_TOP,
            'hot'       => Criteria::SORT_HOT,
            'active'    => Criteria::SORT_ACTIVE,
            'newest'    => Criteria::SORT_NEW,
            'commented' => Criteria::SORT_COMMENTED,

            'ważne'       => Criteria::SORT_TOP,
            'wschodzące'  => Criteria::SORT_HOT,
            'aktywne'     => Criteria::SORT_ACTIVE,
            'najnowsze'   => Criteria::SORT_NEW,
            'komentowane' => Criteria::SORT_COMMENTED,
        ];

        return $routes[$value] ?? $routes['hot'];
    }

    public function resolveTime(?string $value): ?string
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
            'all'      => Criteria::TIME_ALL,
            'wszystko' => Criteria::TIME_ALL,
            '6g'       => Criteria::TIME_6_HOURS,
            '12g'      => Criteria::TIME_12_HOURS,
            '1t'       => Criteria::TIME_WEEK,
            '1r'       => Criteria::TIME_YEAR,
            null       => null,
        ];

        return $routes[$value] ?? $value;
    }

    public function resolveType(?string $value): ?string
    {
        //@todo
        $routes = [
            'article'  => Entry::ENTRY_TYPE_ARTICLE,
            'articles' => Entry::ENTRY_TYPE_ARTICLE,
            'link'     => Entry::ENTRY_TYPE_LINK,
            'links'    => Entry::ENTRY_TYPE_LINK,
            'video'    => Entry::ENTRY_TYPE_VIDEO,
            'videos'   => Entry::ENTRY_TYPE_VIDEO,
            'photo'    => Entry::ENTRY_TYPE_IMAGE,
            'photos'   => Entry::ENTRY_TYPE_IMAGE,

            'artykuł'  => Entry::ENTRY_TYPE_ARTICLE,
            'artykuły' => Entry::ENTRY_TYPE_ARTICLE,
            'linki'    => Entry::ENTRY_TYPE_LINK,
            'zdjęcie'  => Entry::ENTRY_TYPE_IMAGE,
            'zdjęcia'  => Entry::ENTRY_TYPE_IMAGE,
            null       => null,
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
