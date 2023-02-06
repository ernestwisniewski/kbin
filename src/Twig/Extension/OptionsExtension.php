<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\OptionsExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class OptionsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('options_url', [OptionsExtensionRuntime::class, 'optionsUrl']),
            new TwigFunction('options_is_active', [OptionsExtensionRuntime::class, 'options_is_active']),
        ];
    }
}
