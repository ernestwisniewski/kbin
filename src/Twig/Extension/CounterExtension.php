<?php

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
