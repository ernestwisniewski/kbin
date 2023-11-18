<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\DomainExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DomainExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_domain_subscribed', [DomainExtensionRuntime::class, 'isSubscribed']),
            new TwigFunction('is_domain_blocked', [DomainExtensionRuntime::class, 'isBlocked']),
        ];
    }
}
