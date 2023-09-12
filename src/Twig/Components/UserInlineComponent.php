<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\User;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('user_inline')]
final class UserInlineComponent
{
    public User $user;
    public bool $showAvatar = true;
}
