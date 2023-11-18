<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Magazine;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazineLogController extends AbstractController
{
    public function __invoke(Magazine $magazine, MagazineRepository $repository, Request $request): Response
    {
        return $this->render(
            'modlog/magazine.html.twig',
            [
                'magazine' => $magazine,
                'logs' => $repository->findModlog($magazine, $this->getPageNb($request)),
            ]
        );
    }
}
