<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\UserExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class UserExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_user_followed', [UserExtensionRuntime::class, 'isFollowed']),
            new TwigFunction('is_user_blocked', [UserExtensionRuntime::class, 'isBlocked']),
            new TwigFunction('get_reputation_total', [UserExtensionRuntime::class, 'getReputationTotal']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('username', [UserExtensionRuntime::class, 'username'], ['is_safe' => ['html']]),
        ];
    }
}
