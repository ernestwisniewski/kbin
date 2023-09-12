<?php

declare(strict_types=1);

namespace App\Pagination;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class KbinQueryAdapter extends QueryAdapter
{
    public function __construct(
        Query|QueryBuilder $query,
        bool $fetchJoinCollection = true,
        bool $useOutputWalkers = null,
        private ?CacheInterface $cache = null,
    ) {
        parent::__construct($query, $fetchJoinCollection, $useOutputWalkers);
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        if (null === $this->cache) {
            return \count($this->paginator);
        }

        $values = $this->paginator->getQuery()->getParameters()->map(function ($val) {
            return $val->getValue();
        });

        $key = md5($this->paginator->getQuery()->getDQL()).md5(json_encode($values->toArray()));

        return $this->cache->get('pagination_count_'.$key, function (ItemInterface $item) {
            $item->expiresAfter(60);

            return \count($this->paginator);
        });
    }
}
