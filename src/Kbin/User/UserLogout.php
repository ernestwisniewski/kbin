<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\User;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class UserLogout
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack
    ) {
    }

    public function __invoke(): void
    {
        $this->tokenStorage->setToken(null);
        $this->requestStack->getSession()->invalidate();
    }
}
