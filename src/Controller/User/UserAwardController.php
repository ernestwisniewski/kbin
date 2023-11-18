<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAwardController extends AbstractController
{
    public function __invoke(User $user, ?string $awardsCategory, Request $request): Response
    {
        return $this->render(
            'award/list_all.html.twig',
            [
                'user' => $user,
                'category' => $awardsCategory,
                'types' => [],
            ]
        );
    }
}
