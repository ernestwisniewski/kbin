<?php

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
        ];
    }
}
