<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RankingController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        return $this->render(
            'page/ranking.html.twig',
        );
    }
}
