<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Controller\Security;

use App\Controller\AbstractController;
use App\Kbin\User\UserVerify;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class VerifyEmailController extends AbstractController
{
    public function __construct(private UserVerify $userVerify, private UserRepository $userRepository)
    {
    }

    public function __invoke(Request $request): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $this->userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            ($this->userVerify)($request, $user);
        } catch (VerifyEmailExceptionInterface $e) {
            return $this->redirectToRoute('app_register');
        }

        return $this->redirectToRoute('app_login');
    }
}
