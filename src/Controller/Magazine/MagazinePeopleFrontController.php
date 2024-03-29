<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Kbin\People\PeopleByMagazine;
use App\Repository\MagazineRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazinePeopleFrontController extends AbstractController
{
    public function __construct(
        private readonly PeopleByMagazine $peopleByMagazine,
        private readonly MagazineRepository $magazineRepository
    ) {
    }

    public function __invoke(
        Magazine $magazine,
        ?string $category,
        Request $request
    ): Response {
        return $this->render(
            'people/front.html.twig', [
                'magazine' => $magazine,
                'magazines' => array_filter(
                    $this->magazineRepository->findByActivity(),
                    fn ($val) => 'random' !== $val->name && $val !== $magazine
                ),
                'local' => ($this->peopleByMagazine)($magazine),
                'federated' => ($this->peopleByMagazine)($magazine, true),
            ]
        );
    }
}
