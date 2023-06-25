<?php

namespace App\Twig\Components;

use App\Entity\User;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('user_image_component')]
final class UserImageComponent
{
    public User $user;
    public bool $showAvatar = true;
}
