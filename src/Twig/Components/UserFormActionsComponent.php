<?php

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
