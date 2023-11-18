<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Component\HttpFoundation\Request;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

readonly class UserVerify
{
    public function __construct(
        private EmailVerifier $emailVerifier,
    ) {
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function __invoke(Request $request, User $user): void
    {
        $this->emailVerifier->handleEmailConfirmation($request, $user);
    }
}
