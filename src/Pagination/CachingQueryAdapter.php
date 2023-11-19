<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Pagination;

use Pagerfanta\Adapter\AdapterInterface;
use Psr\Cache\CacheItemPoolInterface;

readonly class CachingQueryAdapter implements AdapterInterface
{
    public function __construct(
        private QueryAdapter $queryAdapter,
        private CacheItemPoolInterface $pool,
    ) {
    }

    public function getNbResults(): int
    {
        $nbResult = $this->pool->getItem($this->getCacheKey());

        if ($nbResult->isHit()) {
            return $nbResult->get();
        }

        $query = clone $this->queryAdapter->getQuery();

        $count = $query->orderBy('count')
            ->select("count({$query->getDQLPart('from')[0]->getAlias()}.id) as count")
            ->getQuery()
            ->getSingleScalarResult();

        $nbResult->expiresAfter($count > 25000 ? 86400 : 60);
        $nbResult->set($count);
        $this->pool->save($nbResult);

        return $nbResult->get();
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return $this->queryAdapter->getSlice($offset, $length);
    }

    private function getCacheKey(): string
    {
        $query = clone $this->queryAdapter->getQuery();
        $query = $query->orderBy('id')->getDQL();

        $values = $this->queryAdapter->getQuery()->getParameters()->map(function ($val) {
            $value = $val->getValue();

            if (\is_object($value) && method_exists($value, 'getId')) {
                return sprintf('%s::%s', \get_class($value), $value->getId());
            }

            return $value;
        });

        return 'pagination_count_'.hash('sha256', $query.json_encode($values->toArray()));
    }
}
