<?php

declare(strict_types=1);

namespace App\Schema;

use OpenApi\Attributes as OA;
use Pagerfanta\Pagerfanta;

#[OA\Schema()]
class PaginationSchema implements \JsonSerializable
{
    #[OA\Property(description: 'The total number of items available')]
    public int $count = 0;
    #[OA\Property(description: 'The current page number returned')]
    public int $currentPage = 0;
    #[OA\Property(description: 'The max page number available')]
    public int $maxPage = 0;
    #[OA\Property(description: 'Max number of items per page')]
    public int $perPage = 0;

    public function __construct(Pagerfanta $pagerfanta)
    {
        $this->count = $pagerfanta->count();
        $this->currentPage = $pagerfanta->getCurrentPage();
        $this->maxPage = $pagerfanta->getNbPages();
        $this->perPage = $pagerfanta->getMaxPerPage();
    }

    public function jsonSerialize(): mixed
    {
        return [
            'count' => $this->count,
            'currentPage' => $this->currentPage,
            'maxPage' => $this->maxPage,
            'perPage' => $this->perPage,
        ];
    }
}
