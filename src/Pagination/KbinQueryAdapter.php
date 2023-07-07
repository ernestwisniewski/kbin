<?php

namespace App\Pagination;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;

class KbinQueryAdapter extends QueryAdapter
{
    private int $count;

    public function __construct(Query|QueryBuilder $query, bool $fetchJoinCollection = true, ?bool $useOutputWalkers = null, int $count = 0)
    {
        $this->count = $count;

        parent::__construct($query, $fetchJoinCollection, $useOutputWalkers);
    }

    /**
     * @phpstan-return int<0, max>
     */
//    public function getNbResults(): int
//    {
//        return $this->count;
//    }
}