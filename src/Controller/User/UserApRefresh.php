<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserApRefresh extends AbstractController
{
    public function __construct(private \App\Kbin\User\UserApRefresh $userApRefresh)
    {
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODERATOR")'))]
    public function __invoke(User $user, Request $request): Response
    {
        $this->validateCsrf('user_ap_refresh', $request->request->get('token'));

        ($this->userApRefresh)($user);

        return $this->redirectToRefererOrHome($request);
    }
}
