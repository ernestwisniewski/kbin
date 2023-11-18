<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('user_form_actions')]
final class UserFormActionsComponent
{
    public bool $showLogin = false;
    public bool $showRegister = false;
    public bool $showPasswordReset = false;
    public bool $showResendEmail = false;
}
