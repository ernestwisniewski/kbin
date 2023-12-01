<?php

declare(strict_types=1);

namespace App\Kbin\Pagination;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;

/**
 * @template T
 *
 * @implements PagerfantaInterface<T>
 */
class KbinUnionPagination extends Pagerfanta
{
    /**
     * @phpstan-var positive-int
     */
    private int $currentPage = 1;

    /**
     * @phpstan-var int<0, max>|null
     */
    private ?int $nbResults = null;

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

    /**
     * @return AdapterInterface<T>
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    public function setNbResults(int $nbResults): void
    {
        $this->nbResults = $nbResults;
    }

    /**
     * @return $this<T>
     *
     * @throws LessThan1CurrentPageException  if the current page is less than 1
     * @throws OutOfRangeCurrentPageException if It is not allowed out of range pages and they are not normalized
     */
    public function setCurrentPage(int $currentPage): PagerfantaInterface
    {
        $this->currentPage = $currentPage;
        $this->resetForCurrentPageChange();

        return $this;
    }

    private function resetForCurrentPageChange(): void
    {
        $this->currentPageResults = null;
    }

    /**
     * @phpstan-return positive-int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
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
        $length = $this->getMaxPerPage();

        return $this->getAdapter()->getSlice(0, $length);
    }

    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        if (null === $this->nbResults) {
            $this->nbResults = $this->getAdapter()->getNbResults();
        }

        return $this->nbResults;
    }

    public function setCurrentPageResults(?iterable $paginator): void
    {
        $this->currentPageResults = $paginator;
    }
}
