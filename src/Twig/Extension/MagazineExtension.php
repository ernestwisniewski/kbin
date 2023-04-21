<?php

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
