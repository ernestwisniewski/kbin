<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\User;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('user_box')]
final class UserBoxComponent
{
    public User $user;
    public bool $stretchedLink = true;
}
