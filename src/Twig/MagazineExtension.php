<?php declare(strict_types = 1);

namespace App\Twig;

use App\Twig\Runtime\MagazineRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MagazineExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_magazine_subscribed', [MagazineRuntime::class, 'isSubscribed']),
            new TwigFunction('is_magazine_blocked', [MagazineRuntime::class, 'isBlocked']),
        ];
    }
}
