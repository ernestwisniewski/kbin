<?php

declare(strict_types=1);

namespace App\Pagination;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Adapter which calculates pagination from a Doctrine ORM Query or QueryBuilder.
 *
 * @template T
 *
 * @implements AdapterInterface<T>
 */
class QueryAdapter implements AdapterInterface
{
    /**
     * @var Paginator<T>
     */
    protected readonly Paginator $paginator;

    /**
     * @param bool      $fetchJoinCollection Whether the query joins a collection (true by default)
     * @param bool|null $useOutputWalkers    Flag indicating whether output walkers are used in the paginator
     */
    public function __construct(
        Query|QueryBuilder $query,
        bool $fetchJoinCollection = true,
        bool $useOutputWalkers = null,
    ) {
        $this->paginator = new Paginator($query, $fetchJoinCollection);
        $this->paginator->setUseOutputWalkers($useOutputWalkers);
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        return \count($this->paginator);
    }

    /**
     * @phpstan-param int<0, max> $offset
     * @phpstan-param int<0, max> $length
     *
     * @return \Traversable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $this->paginator->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($length);

        return $this->paginator->getIterator();
    }
}
