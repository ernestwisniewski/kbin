<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminSupportUs extends AbstractController
{
    public function __construct()
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(): Response
    {
        return new Response('');
    }
}
