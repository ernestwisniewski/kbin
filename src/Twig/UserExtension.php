<?php declare(strict_types = 1);

namespace App\Twig;

use App\Twig\Runtime\UserRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UserExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_user_follow', [UserRuntime::class, 'isUserFollow']),
            new TwigFunction('is_user_blocked', [UserRuntime::class, 'isUserBlocked']),
        ];
    }
}
