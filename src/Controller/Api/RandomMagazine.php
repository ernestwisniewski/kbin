<?php declare(strict_types = 1);

namespace App\Controller\Api;

use App\ApiDataProvider\DtoPaginator;
use App\Controller\AbstractController;
use App\Factory\MagazineFactory;
use App\Repository\MagazineRepository;
use Exception;

class RandomMagazine extends AbstractController
{
    public function __construct(
        private MagazineFactory $factory,
        private MagazineRepository $repository,
    ) {
    }

    public function __invoke()
    {
        try {
            $magazine = $this->repository->findRandom();
        } catch (Exception $e) {
            return [];
        }
        $dtos = [$this->factory->createDto($magazine)];

        return new DtoPaginator($dtos, 0, 1, 1);
    }
}
