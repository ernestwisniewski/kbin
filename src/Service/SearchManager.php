<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\SearchRepository;
use Elastica\Query\MultiMatch;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Pagerfanta\PagerfantaInterface;

class SearchManager
{
    public function __construct(private SearchRepository $repo, private RepositoryManagerInterface $manager)
    {
    }

    public function findPaginated(string $val, int $page = 1): PagerfantaInterface
    {
//        if($magazine)
//        {
//            $repo = $this->manager->getRepository('magazines');
//        }

        $query = new MultiMatch();
        $query->setQuery(
            $val
        );

        return $this->repo->search($query, $page);
    }

    public function findRelated(string $query): array
    {
        $repo = $this->manager->getRepository('entries');

        return $repo->find($query, 4);
    }
}
