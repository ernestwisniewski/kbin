<?php

namespace App\Pagination;

use Pagerfanta\Doctrine\ORM\QueryAdapter;

class KbinQueryAdapter extends QueryAdapter
{
    /**
     * @phpstan-return int<0, max>
     */
    public function getNbResults(): int
    {
        return 15000;
    }
}