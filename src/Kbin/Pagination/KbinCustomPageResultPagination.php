<?php

declare(strict_types=1);

namespace App\Kbin\Pagination;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;

/**
 * @template T
 *
 * @implements PagerfantaInterface<T>
 */
class KbinCustomPageResultPagination extends Pagerfanta
{
    /**
     * @phpstan-var iterable<array-key, T>|null
     */
    private ?iterable $currentPageResults = null;

    /**
     * @param AdapterInterface<T> $adapter
     */
    public function __construct(private readonly AdapterInterface $adapter)
    {
        parent::__construct($adapter);
    }

    public function setCurrentPageResults(?iterable $paginator): void
    {
        $this->currentPageResults = $paginator;
    }

    /**
     * @return AdapterInterface<T>
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * @return iterable<array-key, T>
     */
    public function getCurrentPageResults(): iterable
    {
        if (null === $this->currentPageResults) {
            $this->currentPageResults = $this->getCurrentPageResultsFromAdapter();
        }

        return $this->currentPageResults;
    }

    /**
     * @return iterable<array-key, T>
     */
    private function getCurrentPageResultsFromAdapter(): iterable
    {
        $offset = $this->calculateOffsetForCurrentPageResults();
        $length = $this->getMaxPerPage();

        return $this->getAdapter()->getSlice($offset, $length);
    }

    /**
     * @phpstan-return int<0, max>
     */
    private function calculateOffsetForCurrentPageResults(): int
    {
        return ($this->getCurrentPage() - 1) * $this->getMaxPerPage();
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getCurrentPageOffsetStart(): int
    {
        return 0 !== $this->getNbResults() ? $this->calculateOffsetForCurrentPageResults() + 1 : 0;
    }
}
