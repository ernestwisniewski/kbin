<?php

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
        ?bool $useOutputWalkers = null,
        private ?CacheInterface $cache = null,
    ) {
        parent::__construct($query, $fetchJoinCollection, $useOutputWalkers);
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        if(null === $this->cache)  {
            return \count($this->paginator);
        }

        $query = $this->paginator->getQuery()->getDQL();

        return $this->cache->get(('pagination_count_'.md5($query)), function (ItemInterface $item) {
            $item->expiresAfter(60);

            return \count($this->paginator);
        });
    }
}