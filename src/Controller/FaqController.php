<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Component\HttpFoundation\Response;

class FaqController extends AbstractController
{
    public function __construct(private readonly PageRepository $repository)
    {
    }

    public function __invoke(): Response
    {
        return $this->render(
            'page/faq.html.twig',
            [
                'body' => $this->repository->findOneBy(['name' => 'faq'])?->body,
            ]
        );
    }
}
