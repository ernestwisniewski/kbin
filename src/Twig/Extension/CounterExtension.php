<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\CounterExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CounterExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('count_user_boosts', [CounterExtensionRuntime::class, 'countUserBoosts']),
            new TwigFunction('count_user_moderated', [CounterExtensionRuntime::class, 'countUserModerated']),
        ];
    }
}
