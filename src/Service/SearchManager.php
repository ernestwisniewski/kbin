<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\SearchRepository;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;

class SearchManager
{
    public function __construct(
        private readonly SearchRepository $repository,
    ) {
    }

    public function findByTagPaginated(string $val, int $page = 1): PagerfantaInterface
    {
        return new Pagerfanta(new ArrayAdapter([]));
    }

    public function findPaginated(string $val, int $page = 1): PagerfantaInterface
    {
        return $this->repository->search($val, $page);
    }

    public function findByApId(string $url): array
    {
        return $this->repository->findByApId($url);
    }

    public function findRelated(string $query): array
    {
        return [];
    }
}
