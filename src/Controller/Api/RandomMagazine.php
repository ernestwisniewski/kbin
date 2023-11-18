<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Api;

use App\ApiDataProvider\DtoPaginator;
use App\Controller\AbstractController;
use App\Kbin\Magazine\Factory\MagazineFactory;
use App\Repository\MagazineRepository;

class RandomMagazine extends AbstractController
{
    public string $titleTag = 'span';

    public function __construct(
        private readonly MagazineFactory $factory,
        private readonly MagazineRepository $repository,
    ) {
    }

    public function __invoke()
    {
        try {
            $magazine = $this->repository->findRandom();
        } catch (\Exception $e) {
            return [];
        }
        $dtos = [$this->factory->createDto($magazine)];

        return new DtoPaginator($dtos, 0, 1, 1);
    }
}
