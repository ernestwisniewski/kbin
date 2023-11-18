<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Kbin\User\UserSpamProtectionToggle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserSpamProtectionController extends AbstractController
{
    public function __construct(
        private readonly UserSpamProtectionToggle $userSpamProtectionToggle,
    ) {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(User $user, Request $request): Response
    {
        $this->validateCsrf('spam_protection', $request->request->get('token'));

        ($this->userSpamProtectionToggle)($user);

        return $this->redirectToRefererOrHome($request);
    }
}
