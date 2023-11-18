<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\MagazineExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MagazineExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_magazine_subscribed', [MagazineExtensionRuntime::class, 'isSubscribed']),
            new TwigFunction('is_magazine_blocked', [MagazineExtensionRuntime::class, 'isBlocked']),
        ];
    }
}
