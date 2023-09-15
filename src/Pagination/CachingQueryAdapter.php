<?php

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

        $nbResult->expiresAfter(60);
        $nbResult->set($this->queryAdapter->getNbResults());
        $this->pool->save($nbResult);

        return $nbResult->get();
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return $this->queryAdapter->getSlice($offset, $length);
    }

    private function getCacheKey(): string
    {
        $query = $this->queryAdapter->getQuery()->getDQL();
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
