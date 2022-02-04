<?php declare(strict_types=1);

namespace App\Twig;

use App\Twig\Runtime\DomainRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class DomainExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_domain_subscribed', [DomainRuntime::class, 'isSubscribed']),
            new TwigFunction('is_domain_blocked', [DomainRuntime::class, 'isBlocked']),
        ];
    }
}
