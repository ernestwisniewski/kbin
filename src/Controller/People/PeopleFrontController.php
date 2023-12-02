<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\People;

use App\Controller\AbstractController;
use App\Kbin\People\PeopleGeneral;
use App\Repository\MagazineRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PeopleFrontController extends AbstractController
{
    public function __construct(
        private readonly PeopleGeneral $peopleGeneral,
        private readonly MagazineRepository $magazineRepository
    ) {
    }

    public function __invoke(?string $category, Request $request): Response
    {
        return $this->render(
            'people/front.html.twig', [
                'magazines' => array_filter(
                    $this->magazineRepository->findByActivity(),
                    fn ($val) => 'random' !== $val->name
                ),
                'local' => ($this->peopleGeneral)(),
                'federated' => ($this->peopleGeneral)(true),
            ]
        );
    }
}
