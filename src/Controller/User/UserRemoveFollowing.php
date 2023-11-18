<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Kbin\User\UserFollowingDelete;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserRemoveFollowing extends AbstractController
{
    public function __construct(private UserFollowingDelete $userFollowingDelete)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(User $user, Request $request): Response
    {
        $this->validateCsrf('user_remove_following', $request->request->get('token'));

        ($this->userFollowingDelete)($user);

        return $this->redirectToRefererOrHome($request);
    }
}
