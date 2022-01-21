<?php declare(strict_types=1);

namespace App\Repository;

use FOS\ElasticaBundle\Repository;
use Pagerfanta\PagerfantaInterface;

class SearchRepository extends Repository
{
    public function search($query, int $page = 1, int $limit = 48): PagerfantaInterface
    {
        $items = $this->findPaginated($query);
        $items->setMaxPerPage($limit);
        $items->setCurrentPage($page);

        return $items;
    }
}
