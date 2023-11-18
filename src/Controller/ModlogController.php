<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller;

use App\Repository\MagazineLogRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ModlogController extends AbstractController
{
    public function __invoke(MagazineLogRepository $repository, Request $request): Response
    {
        return $this->render(
            'modlog/front.html.twig',
            [
                'logs' => $repository->listAll($this->getPageNb($request)),
            ]
        );
    }
}
