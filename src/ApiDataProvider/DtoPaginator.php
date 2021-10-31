<?php declare(strict_types = 1);

namespace App\ApiDataProvider;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use ArrayIterator;
use EmptyIterator;
use Iterator;
use IteratorAggregate;
use LimitIterator;
use Traversable;

/**
 * Paginator for arrays.
 */
final class DtoPaginator implements IteratorAggregate, PaginatorInterface
{
    private Iterator $iterator;
    private int $firstResult;
    private int $maxResults;
    private int $totalItems;

    public function __construct(array $results, int $firstResult, int $maxResults, int $totalItems)
    {
        if ($maxResults > 0) {
            $this->iterator = new LimitIterator(new ArrayIterator($results), $firstResult, $maxResults);
        } else {
            $this->iterator = new EmptyIterator();
        }
        $this->firstResult = $firstResult;
        $this->maxResults  = $maxResults;
        $this->totalItems  = $totalItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPage(): float
    {
        if (0 >= $this->maxResults) {
            return 1.;
        }

        return floor($this->firstResult / $this->maxResults) + 1.;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastPage(): float
    {
        if (0 >= $this->maxResults) {
            return 1.;
        }

        return ceil($this->totalItems / $this->maxResults) ?: 1.;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsPerPage(): float
    {
        return (float) $this->maxResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalItems(): float
    {
        return (float) $this->totalItems;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return iterator_count($this->iterator);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Traversable
    {
        return $this->iterator;
    }
}
