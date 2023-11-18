<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Kbin\User\UserDelete;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserDeleteController extends AbstractController
{
    public function __construct(private UserDelete $userDelete)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function purgeContent(User $user, Request $request): Response
    {
        $this->validateCsrf('user_purge_content', $request->request->get('token'));

        ($this->userDelete)($user, true, true);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function deleteContent(User $user, Request $request): Response
    {
        $this->validateCsrf('user_delete_content', $request->request->get('token'));

        ($this->userDelete)($user, false, true);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function purgeAccount(User $user, Request $request): Response
    {
        $this->validateCsrf('user_purge_account', $request->request->get('token'));

        ($this->userDelete)($user, true);

        return $this->redirectToRoute('front');
    }

    #[IsGranted('ROLE_ADMIN')]
    public function deleteAccount(User $user, Request $request): Response
    {
        $this->validateCsrf('user_delete_account', $request->request->get('token'));

        ($this->userDelete)($user);

        return $this->redirectToRoute('front');
    }
}
