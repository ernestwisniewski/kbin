<?php declare(strict_types = 1);

namespace App\Repository;

class Criteria
{
    private int $page;

    public function __construct(int $page = 1)
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }
}
