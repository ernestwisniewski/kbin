<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\ContextExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ContextExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('route_name_contains', [ContextExtensionRuntime::class, 'routeNameContains']),
        ];
    }
}
