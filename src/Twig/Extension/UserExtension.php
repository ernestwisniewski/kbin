<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\UserExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class UserExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('username', [UserExtensionRuntime::class, 'username'], ['is_safe' => ['html']]),
        ];
    }
}
