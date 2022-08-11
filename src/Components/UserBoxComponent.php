<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\User;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('user_box')]
class UserBoxComponent
{
    public User $user;
}
