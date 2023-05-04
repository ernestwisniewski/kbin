<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Entity\User;

abstract class Criteria
{
    public const ENTRY_TYPE_ARTICLE = 'article';
    public const ENTRY_TYPE_LINK = 'link';

    public const FRONT_FEATURED = 'featured';
    public const FRONT_SUBSCRIBED = 'subscribed';
    public const FRONT_ALL = 'all';
    public const FRONT_MODERATED = 'moderated';
    public const SORT_ACTIVE = 'active';
    public const SORT_HOT = 'hot';
    public const SORT_NEW = 'newest';
    public const SORT_DEFAULT = self::SORT_HOT;

    public const SORT_OLD = 'oldest';
    public const SORT_TOP = 'top';
    public const SORT_COMMENTED = 'commented';

    public const TIME_3_HOURS = '3hours';
    public const TIME_6_HOURS = '6hours';
    public const TIME_12_HOURS = '12hours';
    public const TIME_DAY = 'day';
    public const TIME_WEEK = 'week';
    public const TIME_MONTH = 'month';
    public const TIME_YEAR = 'year';
    public const TIME_ALL = '∞';

    public const AP_ALL = 'all';
    public const AP_LOCAL = 'local';
    public const AP_FEDERATED = 'federated';

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
    public bool $favourite = false;
    public ?string $type = null;
    public string $sortOption = EntryRepository::SORT_DEFAULT;
    public string $time = EntryRepository::TIME_DEFAULT;
    public string $visibility = VisibilityInterface::VISIBILITY_VISIBLE;
    public string $federation = self::AP_ALL;
    public bool $subscribed = false;
    public ?string $tag = null;
    public ?string $domain = null;

    public function __construct(int $page)
    {
        $this->page = $page;
    }

    public function setFederation($feed): self
    {
        $this->federation = $feed;

        return $this;
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
        // @todo getRoute EntryManager
        $routes = [
            'top' => Criteria::SORT_TOP,
            'hot' => Criteria::SORT_HOT,
            'active' => Criteria::SORT_ACTIVE,
            'newest' => Criteria::SORT_NEW,
            'oldest' => Criteria::SORT_OLD,
            'commented' => Criteria::SORT_COMMENTED,

            'ważne' => Criteria::SORT_TOP,
            'gorące' => Criteria::SORT_HOT,
            'aktywne' => Criteria::SORT_ACTIVE,
            'najnowsze' => Criteria::SORT_NEW,
            'najstarsze' => Criteria::SORT_OLD,
            'komentowane' => Criteria::SORT_COMMENTED,
        ];

        return $routes[$value] ?? $routes['hot'];
    }

    public function resolveTime(?string $value): ?string
    {
        // @todo
        $routes = [
            '3h' => Criteria::TIME_3_HOURS,
            '6h' => Criteria::TIME_6_HOURS,
            '12h' => Criteria::TIME_12_HOURS,
            '1d' => Criteria::TIME_DAY,
            '1w' => Criteria::TIME_WEEK,
            '1m' => Criteria::TIME_MONTH,
            '1y' => Criteria::TIME_YEAR,
            '∞' => Criteria::TIME_ALL,
            'all' => Criteria::TIME_ALL,
            'wszystko' => Criteria::TIME_ALL,
            '3g' => Criteria::TIME_3_HOURS,
            '6g' => Criteria::TIME_6_HOURS,
            '12g' => Criteria::TIME_12_HOURS,
            '1t' => Criteria::TIME_WEEK,
            '1r' => Criteria::TIME_YEAR,
        ];

        return $routes[$value] ?? null;
    }

    public function resolveType(?string $value): ?string
    {
        // @todo
        $routes = [
            'article' => Entry::ENTRY_TYPE_ARTICLE,
            'articles' => Entry::ENTRY_TYPE_ARTICLE,
            'link' => Entry::ENTRY_TYPE_LINK,
            'links' => Entry::ENTRY_TYPE_LINK,
            'video' => Entry::ENTRY_TYPE_VIDEO,
            'videos' => Entry::ENTRY_TYPE_VIDEO,
            'photo' => Entry::ENTRY_TYPE_IMAGE,
            'photos' => Entry::ENTRY_TYPE_IMAGE,
            'image' => Entry::ENTRY_TYPE_IMAGE,
            'images' => Entry::ENTRY_TYPE_IMAGE,

            'artykuł' => Entry::ENTRY_TYPE_ARTICLE,
            'artykuły' => Entry::ENTRY_TYPE_ARTICLE,
            'linki' => Entry::ENTRY_TYPE_LINK,
            'obraz' => Entry::ENTRY_TYPE_IMAGE,
            'obrazy' => Entry::ENTRY_TYPE_IMAGE,
        ];

        return $routes[$value] ?? null;
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

    public function getSince(): \DateTimeImmutable
    {
        $since = new \DateTimeImmutable('@'.time());

        return match ($this->time) {
            Criteria::TIME_YEAR => $since->modify('-1 year'),
            Criteria::TIME_MONTH => $since->modify('-1 month'),
            Criteria::TIME_WEEK => $since->modify('-1 week'),
            Criteria::TIME_DAY => $since->modify('-1 day'),
            Criteria::TIME_12_HOURS => $since->modify('-12 hours'),
            Criteria::TIME_6_HOURS => $since->modify('-6 hours'),
            Criteria::TIME_3_HOURS => $since->modify('-3 hours'),
            default => throw new \LogicException(),
        };
    }
}
