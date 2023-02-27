<?php

declare(strict_types=1);

namespace App\Repository;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;

class SearchRepository
{
    public function search($query, int $page = 1, int $limit = 48): PagerfantaInterface
    {
        return new Pagerfanta(new ArrayAdapter([]));
    }
}
