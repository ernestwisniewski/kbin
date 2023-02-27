<?php

declare(strict_types=1);

namespace App\Service;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;

class SearchManager
{
    public function findByTagPaginated(string $val, int $page = 1): PagerfantaInterface
    {
        return new Pagerfanta(new ArrayAdapter([]));
    }

    public function findMagazinesPaginated(string $magazine, int $page = 1): PagerfantaInterface
    {
        return new Pagerfanta(new ArrayAdapter([]));
    }

    public function findPaginated(string $val, int $page = 1): PagerfantaInterface
    {
        return new Pagerfanta(new ArrayAdapter([]));
    }

    public function findRelated(string $query): array
    {
        return [];
    }
}
