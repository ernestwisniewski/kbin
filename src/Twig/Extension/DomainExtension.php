<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\DomainExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
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
