<?php declare(strict_types=1);

namespace App\Repository;

use Elastica\Query;
use FOS\ElasticaBundle\Repository;
use Pagerfanta\PagerfantaInterface;

class SearchRepository extends Repository
{
    public function search(string $searchTerm, int $page = 1, int $limit = 48): PagerfantaInterface
    {
            $fieldQuery = new Query\MultiMatch();

            $fieldQuery->setQuery(
                $searchTerm
            );
            $items     = $this->findPaginated($fieldQuery);
            $items->setMaxPerPage($limit);
            $items->setCurrentPage($page);

            return $items;
    }
}
