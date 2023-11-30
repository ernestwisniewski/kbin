<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\CategoryExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategoryExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_category_subscribed', [CategoryExtensionRuntime::class, 'isSubscribed']),
            new TwigFunction('category_url', [CategoryExtensionRuntime::class, 'categoryUrl']),
        ];
    }
}
