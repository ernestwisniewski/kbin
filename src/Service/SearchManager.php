<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\SearchRepository;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MultiMatch;
use Elastica\Query\Terms;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Pagerfanta\PagerfantaInterface;

class SearchManager
{
    public function __construct(private SearchRepository $repo, private RepositoryManagerInterface $manager)
    {
    }

    public function findPaginated(string $val, int $page = 1): PagerfantaInterface
    {
        $query = new MultiMatch();
        $query->setQuery(
            $val
        );

        return $this->repo->search($query, $page);
    }

    public function findByTagPaginated(string $val, int $page = 1): PagerfantaInterface
    {
        $boolQuery = new BoolQuery();
        $tagQuery  = new Terms('tags', [$val]);
        $boolQuery->addMust($tagQuery);

        $query = new Query($boolQuery);
        $query = $query->addSort([
            'createdAt' => ['order' => 'desc'],
        ]);

        return $this->repo->search($query, $page);
    }

    public function findMagazinesPaginated(string $magazine, int $page = 1): PagerfantaInterface
    {
        $repo = $this->manager->getRepository('magazines');

        $query = new MultiMatch();
        $query->setQuery(
            $magazine
        );

        return $repo->findPaginated($query)
            ->setCurrentPage($page)
            ->setMaxPerPage(50);
    }

    public function findRelated(string $query): array
    {
        $repo = $this->manager->getRepository('entries');

        return $repo->find($query, 4);
    }
}
