<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\LinkExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LinkExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_rel', [LinkExtensionRuntime::class, 'getRel']),
            new TwigFunction('get_url_fragment', [LinkExtensionRuntime::class, 'getHtmlClass']),
            new TwigFunction('get_url_domain', [LinkExtensionRuntime::class, 'getLinkDomain']),
        ];
    }
}
