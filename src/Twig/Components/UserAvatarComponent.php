<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\User;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('user_avatar')]
final class UserAvatarComponent
{
    public int $width = 32;
    public int $height = 32;
    public User $user;
    public bool $asLink = false;
}
