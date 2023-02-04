<?php

namespace App\Twig\Components;

use App\Entity\User;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('user_avatar')]
final class UserAvatarComponent
{
    public int $width = 32;
    public int $height = 32;
    public User $user;
}
