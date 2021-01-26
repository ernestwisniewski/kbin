<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Magazine;

class Criteria
{
    private int $page;
    private ?Magazine $magazine;

    public function __construct(int $page = 1, ?Magazine $magazine = null)
    {
        $this->page     = $page;
        $this->magazine = $magazine;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }
}
